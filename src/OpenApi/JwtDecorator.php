<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model;

final class JwtDecorator implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);

        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'johndoe@example.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'xxxxxxxx',
                ],
            ],
        ]);

        $post = new Model\Operation(
            'postCredentialsItem',
            ['Token'],
            [
                '200' => [
                    'description' => 'Get JWT token',
                    'content' => [],
                ],
                '400' => [
                    'description' => 'Bad request.',
                    'content' => [],
                ],
                '401' => [
                    'description' => 'Invalid credentials.',
                    'content' => [],
                ],
                '5XX' => [
                    'description' => 'Unexpected error.',
                    'content' => [],
                ]
            ],
            'Get JWT token.',
            'Get the **Json Web Token** with **username** and **password** credentials.',
            null,
            [],
            new Model\RequestBody(
                'Generate new JWT Token',
                new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Credentials',
                        ],
                    ],
                ]),
            ),
        );

        $pathItem = new Model\PathItem(
            '',
            '',
            '',
            null,
            null,
            $post,
            null,
            null,
            null,
            null,
            null
        );

        $openApi->getPaths()->addPath('/api/login_check', $pathItem);

        return $openApi;
    }
}