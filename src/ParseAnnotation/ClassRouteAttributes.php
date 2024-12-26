<?php

namespace Landao\WebmanCore\ParseAnnotation;

use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;
use Landao\WebmanCore\Annotation\Router\Defaults;
use Landao\WebmanCore\Annotation\Router\Group;
use Landao\WebmanCore\Annotation\Router\Middleware;
use Landao\WebmanCore\Annotation\Router\Prefix;
use Landao\WebmanCore\Annotation\Router\Resource;
use ReflectionClass;

class ClassRouteAttributes
{
    public function __construct(
        private ReflectionClass $class
    ) {
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function prefix(): ?string
    {
        /** @var Prefix $attribute */
        if (! $attribute = $this->getAttribute(Prefix::class)) {
            return null;
        }

        return $attribute->prefix;
    }



    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function groups(): array
    {
        $groups = [];

        /** @var ReflectionClass[] $attributes */
        $attributes = $this->class->getAttributes(Group::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) > 0) {
            foreach ($attributes as $attribute) {
                $attributeClass = $attribute->newInstance();
                $groups[] = array_filter([
                    'prefix' => $attributeClass->prefix,
                    'as' => $attributeClass->as,
                ]);
            }
        } else {
            $groups[] = array_filter([
                'prefix' => $this->prefix(),
            ]);
        }

        return $groups;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function resource(): ?string
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->resource;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function parameters(): array | string | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->parameters;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function shallow(): bool | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->shallow;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function apiResource(): ?string
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->apiResource;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function except(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->except;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function only(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->only;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function names(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->names;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function middleware(): array
    {
        /** @var Middleware $attribute */
        if (! $attribute = $this->getAttribute(Middleware::class)) {
            return [];
        }

        return $attribute->middleware;
    }




    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function defaults(): array
    {
        $defaults = [];
        /** @var ReflectionClass[] $attributes */
        $attributes = $this->class->getAttributes(Defaults::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $attributeClass = $attribute->newInstance();
            $defaults[$attributeClass->key] = $attributeClass->value;
        }

        return $defaults;
    }

    protected function getAttribute(string $attributeClass): ?RouteAttribute
    {
        $attributes = $this->class->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF);

        if (! count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}