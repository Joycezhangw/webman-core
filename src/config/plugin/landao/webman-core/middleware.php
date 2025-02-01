<?php
use Landao\WebmanCore\Validation\ValidatorMiddleware;
use Landao\WebmanCore\Middleware\ApiCaseConverter;

return [
    '@' => [
        ValidatorMiddleware::class,
    ]
];