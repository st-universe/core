<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Emitter;

use Psr\Http\Message\ResponseInterface;

final class ResponseEmitter extends \Slim\ResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        // @todo enable CORS

        parent::emit($response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization'
            )
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
        );
    }
}