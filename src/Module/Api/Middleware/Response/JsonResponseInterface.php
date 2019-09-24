<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Psr\Http\Message\ResponseInterface;

interface JsonResponseInterface extends ResponseInterface
{
    public function withData($data): JsonResponseInterface;

    public function withError(int $errorCode, ?string $description = null): self;
}