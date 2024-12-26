<?php

namespace Landao\WebmanCore\Http;


use Illuminate\Contracts\Validation\Validator;
use Landao\WebmanCore\Validation\ValidatorFactory;
use support\Request;
use Webman\App;

/**
 * Todo：需要修改 config/process.php 修改 webman.constructor.requestClass
 * 需要修改 config/app.php 修改 request_class
 *
 * 否则，无法在控制器中使用依赖注入
 */
class FormRequest extends Request
{
    /**
     * 表单验证
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function validate(array $rules = [], array $messages = [], array $customAttributes = []): Validator
    {
        /** @var ValidatorFactory $validate */
        $validator = App::container()->get(ValidatorFactory::class);
        $data = array_merge($this->all(), $this->properties);
        return $validator->make($data, $rules, $messages, $customAttributes);
    }

}