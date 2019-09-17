<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;

final class ReponseFactory extends ResponseFactory
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(
        int $code = StatusCodeInterface::STATUS_OK,
        string $reasonPhrase = ''
    ): ResponseInterface {
        $res = new JsonResponse($code);

        if ($reasonPhrase !== '') {
            $res = $res->withStatus($code, $reasonPhrase);
        }

        return $res;
    }
}