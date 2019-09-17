<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Psr\Http\Message\ResponseInterface;

interface JsonResponseInterface extends ResponseInterface
{
    public function withData($data, ?int $status = 200): JsonResponseInterface;

    public function withError(string $type, ?string $description = null, ?int $status = 200): self;
}