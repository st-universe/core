<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Slim\Psr7\Response;

final class JsonResponse extends Response implements JsonResponseInterface
{

    public function withData($data): JsonResponseInterface
    {
        parent::getBody()->write(
            json_encode([
                'data' => $data
            ])
        );

        return $this;
    }

    public function withError(int $errorCode, ?string $description = null): JsonResponseInterface
    {
        parent::getBody()->write(
            json_encode([
                'error' => [
                    'code' => $errorCode,
                    'description' => $description,
                ]
            ])
        );

        return $this;
    }
}