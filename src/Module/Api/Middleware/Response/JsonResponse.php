<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Slim\Psr7\Response;

final class JsonResponse extends Response implements JsonResponseInterface
{

    public function withData($data, ?int $status = 200): JsonResponseInterface
    {
        parent::getBody()->write(
            json_encode([
                'statusCode' => $status,
                'data' => $data
            ])
        );

        return $this;
    }

    public function withError(string $type, ?string $description = null, ?int $status = 200): JsonResponseInterface
    {
        parent::getBody()->write(
            json_encode([
                'statusCode' => $status,
                'error' => [
                    'type' => $type,
                    'description' => $description,
                ]
            ])
        );

        return $this;
    }
}