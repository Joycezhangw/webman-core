<?php

namespace Landao\WebmanCore\ParseAnnotation;

use Webman\Route as WebManRouter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;
use Landao\WebmanCore\Annotation\Router\Defaults;
use Landao\WebmanCore\Annotation\Router\Fallback;
use Landao\WebmanCore\Annotation\Router\Route;
use Symfony\Component\Finder\Finder;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
use Throwable;

class RouteRegistrar
{
    /**
     * 路由基础路径
     * @var string
     */
    protected string $basePath;

    /**
     * 根命名空间
     * @var string
     */
    protected string $rootNamespace;

    /**
     * 路由中间件
     * @var array
     */
    protected array $middleware = [];

    /**
     * 路由别名，设置路由name的时候，追加的前缀名称
     * @var string
     */
    protected string $routeNameAlias = '';

    public function __construct()
    {

        $this->useBasePath(app_path());
    }

    /**
     * 定义一个路由组
     *
     * 该方法用于创建一个路由组，其中所有路由都共享相同的前缀，这有助于组织和管理大量路由
     * 它接受一个包含路由组前缀的选项数组和一个闭包，该闭包定义了属于该组的路由
     *
     * @param array $options 包含路由组配置选项的数组，如路由前缀
     * @param callable $routes 一个闭包，用于定义属于该路由组的路由
     * @return $this 返回当前实例，支持链式调用
     */
    public function group(array $options, $routes): self
    {
        // 调用路由构建器的group方法，传入前缀和路由定义闭包
        WebManRouter::group($options['prefix'] ?? '', $routes);
        // 将路由别名设置为路由组选项中的as值
        $this->routeNameAlias = $options['as'] ?? '';

        return $this;
    }

    /**
     * 设置基础路径
     *
     * 该方法用于动态设置类的基础路径属性，确保路径使用正确的目录分隔符
     * 它接受一个字符串参数 $basePath，表示待设置的基础路径
     * 方法内部会将 $basePath 中的 '/' 和 '\' 替换为正确的目录分隔符，以适配不同的操作系统
     *
     * @param string $basePath 待设置的基础路径
     * @return self 返回当前对象实例，支持链式调用
     * */
    public function useBasePath(string $basePath): self
    {
        $this->basePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $basePath);

        return $this;
    }

    /**
     * 设置命名空间
     * @param string $rootNamespace
     * @return $this
     */
    public function useRootNamespace(string $rootNamespace): self
    {
        $this->rootNamespace = rtrim(str_replace('/', '\\', $rootNamespace), '\\') . '\\';

        return $this;
    }

    /**
     * 设置或添加中间件到当前实例
     *
     * 该方法允许在处理请求时指定一个或多个中间件。中间件可以是字符串（表示单个中间件）
     * 或数组（表示多个中间件）。通过此方法，可以灵活地在不同场景下应用不同的中间件处理逻辑
     *
     * @param string|array $middleware 中间件的名称，可以是单个中间件的字符串名称或多个中间件的数组
     * @return $this 支持链式调用，返回当前实例
     */
    public function useMiddleware(string|array $middleware): self
    {
        // 将中间件参数转换为数组并存储，确保即使传入单个中间件名称也能正确处理
        $this->middleware = Arr::wrap($middleware);

        return $this;
    }

    /**
     * 获取中间件数组
     *
     * 此方法用于返回当前实例中的中间件数组，如果中间件数组未设置，则返回空数组
     * 中间件是一组在请求处理过程中执行的函数，用于在请求到达路由或控制器之前进行处理
     *
     * @return array 中间件数组，包含一系列中间件
     */
    public function middleware(): array
    {
        return $this->middleware ?? [];
    }

    /**
     * 注册目录中的文件
     *
     * 该方法用于扫描指定目录中的文件，并根据提供的模式注册这些文件
     * 主要用于批量处理文件，如动态加载和注册某个目录下的所有PHP文件
     *
     * @param string|array $directories 目录路径，可以是单个目录字符串或目录数组
     * @param array $patterns 文件模式数组，用于指定要注册的文件类型，默认为['*.php']
     * @param array $notPatterns 排除模式数组，用于指定不应注册的文件类型
     * @return void
     */
    public function registerDirectory(string|array $directories, array $patterns = [], array $notPatterns = []): void
    {
        // 将目录参数转换为数组，确保后续操作可以统一处理
        $directories = Arr::wrap($directories);
        // 如果未提供文件模式，则默认为['*.php']，即只注册PHP文件
        $patterns = $patterns ?: ['*.php'];

        // 使用Finder类扫描指定目录中的文件，并根据提供的包含和排除模式筛选文件
        // 最后按文件名排序，以便后续处理
        $files = (new Finder())->files()->in($directories)->name($patterns)->notName($notPatterns)->sortByName();
        // 遍历找到的文件集合，对每个文件调用registerFile方法进行注册
        collect($files)->each(fn(SplFileInfo $file) => $this->registerFile($file));
    }

    /**
     * 注册文件以供进一步处理
     *
     * 该方法接受一个文件路径字符串或一个SplFileInfo对象作为输入，并执行以下操作：
     * 1. 如果输入是字符串，则使用它来创建一个SplFileInfo对象
     * 2. 从文件信息中获取完全限定类名
     * 3. 处理与该类名相关的属性
     *
     * @param string|SplFileInfo $path 文件路径字符串或SplFileInfo对象，表示要注册的文件
     * @return void
     */
    public function registerFile(string|SplFileInfo $path): void
    {
        // 如果$path是字符串，则将其转换为SplFileInfo对象
        if (is_string($path)) {
            $path = new SplFileInfo($path);
        }
        // 根据文件信息获取完全限定类名
        $fullyQualifiedClassName = $this->fullQualifiedClassNameFromFile($path);
        // 处理与完全限定类名相关的属性
        $this->processAttributes($fullyQualifiedClassName);
    }

    /**
     * 注册一个类，以便其属性和方法可以被处理
     *
     * 该方法主要用于在特定的类被实例化或调用之前，对其进行预处理
     * 它通过调用内部方法processAttributes来实现对类的处理
     *
     * @param string $class 需要注册和处理的类名
     *
     * @return void 该方法不返回任何值
     */
    public function registerClass(string $class): void
    {
        // 对给定的类进行属性处理
        $this->processAttributes($class);
    }


    /**
     * 根据文件信息获取全限定类名
     *
     * 该方法用于将文件路径转换为对应的全限定类名它首先会从文件的绝对路径中移除基础路径，
     * 然后将文件路径的目录分隔符替换为命名空间分隔符，并移除类名对应的文件后缀最后，
     * 它会将根命名空间追加到处理后的类名前，以得到完整的命名空间及类名
     *
     * @param SplFileInfo $file 文件信息对象，用于获取文件的路径信息
     * @return string 返回转换后的全限定类名
     */
    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): string
    {
        // 从文件的绝对路径中移除基础路径，并确保路径开头没有多余的目录分隔符
        $class = trim(Str::replaceFirst($this->basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);


        // 将文件路径中的目录分隔符替换为命名空间分隔符，同时确保类名以命名空间开头
        // 并且移除类名对应的文件后缀
        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'app\\'],
            ['\\', 'app\\'],
            Str::replaceLast('.php', '', $class)
        );

        // 将根命名空间追加到处理后的类名前，以得到完整的命名空间及类名
        return $this->rootNamespace . $class;
    }

    /**
     * 处理指定类的路由属性
     *
     * 本方法通过反射检查给定类是否存在，如果存在，则创建该类的反射实例，并进一步处理其路由属性
     * 如果类不存在，则直接返回，不进行任何操作
     *
     * @param string $className 需要处理的类的名称
     */
    protected function processAttributes(string $className): void
    {
        // 检查给定的类是否存在，如果不存在则直接返回
        if (!class_exists($className)) {
            return;
        }

        // 创建反射类实例，用于后续的属性和方法检查
        $class = new ReflectionClass($className);

        // 创建ClassRouteAttributes实例，用于获取类的路由属性
        $classRouteAttributes = new ClassRouteAttributes($class);

        // 如果类属性配置了资源路由，则注册资源路由
        if ($classRouteAttributes->resource()) {
            $this->registerResource($class, $classRouteAttributes);
        }

        // 获取类属性中的组路由配置
        $groups = $classRouteAttributes->groups();

        // 遍历每个组路由配置，注册组路由
        foreach ($groups as $group) {
            // 使用闭包函数注册组路由，组内的路由注册操作委托给registerRoutes方法
            WebManRouter::group($group['prefix'] ?? '', fn() => $this->registerRoutes($class, $classRouteAttributes));
        }
    }

    /**
     * 注册资源路由
     *
     * 该方法用于根据反射类和类路由属性来注册一个资源路由
     * 它通过创建一个路由组，应用前缀和获取的路由规则来实现资源路由的注册
     *
     * @param ReflectionClass $class 反射类对象，用于获取类的相关信息
     * @param ClassRouteAttributes $classRouteAttributes 类路由属性对象，包含路由的前缀等信息
     *
     * @return void 该方法没有返回值
     */
    protected function registerResource(ReflectionClass $class, ClassRouteAttributes $classRouteAttributes): void
    {
        // 创建路由组，应用前缀并设置路由规则
        WebManRouter::group($classRouteAttributes->prefix(),
            $this->getRoutes($class, $classRouteAttributes));
    }

    /**
     * 注册类中的路由
     *
     * 该方法通过反射类和类路由属性来注册类中的所有方法作为路由
     * 它会遍历类中的每个方法，获取方法上的路由属性，并根据这些属性创建路由
     *
     * @param ReflectionClass $class 反射类对象，用于获取类的方法信息
     * @param ClassRouteAttributes $classRouteAttributes 类路由属性对象，包含类级别的路由属性
     */
    protected function registerRoutes(ReflectionClass $class, ClassRouteAttributes $classRouteAttributes): void
    {
        // 遍历类中的每个方法
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // 获取当前方法的路由属性
            list($attributes, $fallbackAttributes) = $this->getAttributesForTheMethod($method);

            // 遍历当前方法的路由属性
            foreach ($attributes as $attribute) {

                try {
                    // 实例化路由属性对象
                    $attributeClass = $attribute->newInstance();
                } catch (Throwable $throwable) {
                    // 如果实例化失败，则跳过当前属性
                    continue;
                }

                // 如果当前属性不是Route实例，则跳过
                if (!$attributeClass instanceof Route) {
                    continue;
                }

                // 获取当前路由的HTTP方法和动作
                list($httpMethods, $action) = $this->getHTTPMethodsAndAction($attributeClass, $method, $class);
                // 创建路由
                $route = WebManRouter::add($httpMethods, $attributeClass->uri, $action);
                if ($attributeClass->name) {
                    $route->name($this->routeNameAlias . $attributeClass->name);
                }

                // 设置路由的中间件
                $this->addMiddlewareToRoute($classRouteAttributes, $attributeClass, $route);

                // 如果存在fallback路由属性，则设置fallback路由
                if (count($fallbackAttributes) > 0) {
                    WebManRouter::fallback($fallbackAttributes);
                }
            }
        }
    }


    /**
     * 向路由添加中间件
     *
     * 此函数负责将类级别的中间件和方法级别的中间件添加到Webman框架的路由配置中
     * 它通过合并中间件数组并将其传递给路由的middleware方法来实现
     *
     * @param ClassRouteAttributes $classRouteAttributes 类级别的路由属性，包含类中间件信息
     * @param Route $attributeClass 方法级别的路由属性，包含方法中间件信息
     * @param \Webman\Route\Route $route Webman框架的路由实例，用于应用中间件
     *
     * @return void
     */
    public function addMiddlewareToRoute(ClassRouteAttributes $classRouteAttributes, Route $attributeClass, \Webman\Route\Route $route): void
    {
        // 获取类级别的中间件
        $classMiddleware = $classRouteAttributes->middleware();
        // 获取方法级别的中间件
        $methodMiddleware = $attributeClass->middleware;
        // 合并框架中间件、类中间件和方法中间件，并应用到路由
        $route->middleware([...$this->middleware, ...$classMiddleware, ...$methodMiddleware]);
    }


    /**
     * 获取方法的属性
     *
     * 本函数通过反射获取方法上特定类型的属性信息，包括路由、条件、默认值、回退逻辑等
     * 这些属性用于进一步处理和解析方法的路由和行为规则
     *
     * @param ReflectionMethod $method 反射方法对象，用于获取方法的属性信息
     * @return array 返回一个包含各种类型属性的数组
     */
    public function getAttributesForTheMethod(ReflectionMethod $method): array
    {
        // 定义一个辅助函数来获取特定类型的属性
        $getAttribute = function ($class) use ($method) {
            return $method->getAttributes($class, ReflectionAttribute::IS_INSTANCEOF);
        };

        // 获取路由属性，用于定义方法的路由规则
        $attributes = $getAttribute(RouteAttribute::class);

        // 获取默认值属性，用于定义方法的默认参数或返回值
        $defaultAttributes = $getAttribute(Defaults::class);

        // 获取回退逻辑属性，用于定义方法的回退行为
        $fallbackAttributes = $getAttribute(Fallback::class);

        // 返回收集到的各种类型属性，供进一步处理使用
        return [$attributes, $defaultAttributes, $fallbackAttributes];
    }


    /**
     * 获取HTTP方法和操作（路由和动作）
     *
     * 此函数用于解析给定的路由类和反射方法/类对象，以确定HTTP方法（如GET、POST等）
     * 以及应执行的动作（控制器类名和方法名）这主要用于在路由配置中将请求映射到正确的动作
     *
     * @param Route $attributeClass 路由类，包含HTTP方法信息
     * @param ReflectionMethod $method 反射方法对象，用于获取方法名
     * @param ReflectionClass $class 反射类对象，用于获取类名
     *
     * @return array 返回一个包含HTTP方法和操作的数组
     */
    public function getHTTPMethodsAndAction(Route $attributeClass, ReflectionMethod $method, ReflectionClass $class): array
    {
        // 获取HTTP方法数组，这些方法与当前路由关联
        $httpMethods = $attributeClass->methods;

        // 确定动作如果方法名是'__invoke'，则动作是类名本身；否则，动作是类名和方法名的数组
        $action = $method->getName() === '__invoke' ? $class->getName() : [$class->getName(), $method->getName()];

        // 返回包含HTTP方法和动作的数组
        return [$httpMethods, $action];
    }


    /**
     * 根据类和类路由属性获取路由配置闭包
     *
     * 该方法用于动态生成路由配置，基于给定的类和类路由属性。通过反射机制和类路由属性，
     * 它能够为特定的资源控制器生成一套标准路由，并应用相关的路由限制和中间件。
     *
     * @param ReflectionClass $class 资源控制器类的反射对象，用于获取类名等信息
     * @param ClassRouteAttributes $classRouteAttributes 类路由属性对象，包含了路由的各种配置信息
     *
     * @return \Closure 返回一个闭包，当调用时，会根据提供的类和路由属性注册路由
     */
    public function getRoutes(ReflectionClass $class, ClassRouteAttributes $classRouteAttributes): \Closure
    {
        // 返回一个闭包，用于延迟路由的注册，以便于在需要时进行动态配置
        return function () use ($class, $classRouteAttributes) {
            // 根据类路由属性中的资源名称和控制器类名生成基础资源路由
            $route = WebManRouter::resource($classRouteAttributes->resource(), $class->getName());

            // 定义一系列可能的路由配置方法，这些方法可用于进一步限制和配置资源路由
//        $methods = [
//            'only',
//            'except',
//            'names',
//            'parameters',
//            'shallow',
//        ];
//
//        // 遍历每个配置方法，根据类路由属性中的配置进一步定制路由
//        foreach ($methods as $method) {
//            $value = $classRouteAttributes->$method();
//
//            // 如果当前配置方法的值不为空，则应用该配置到路由中
//            if ($value !== null) {
//                $route->$method($value);
//            }
//        }

            // 合并系统级中间件和类路由属性中定义的中间件，应用到路由中
            $route->middleware([...$this->middleware, ...$classRouteAttributes->middleware()]);
        };
    }

}