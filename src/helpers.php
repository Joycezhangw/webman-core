<?php
if (!function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = new \Landao\WebmanCore\Validation\ValidatorFactory();
        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

use Landao\WebmanCore\Tenant\Tenancy;

if (!function_exists('tenancy')) {
    /**
     * 获取租户管理实例
     *
     * @return Tenancy
     */
    function tenancy(): Tenancy
    {
        return Tenancy::getInstance();
    }
}

if (!function_exists('tenant')) {
    /**
     * 获取当前租户实例
     *
     * @return \Landao\WebmanCore\Tenant\Model\Tenant|null
     */
    function tenant()
    {
        return tenancy()->getTenant();
    }
}