<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class KickPlayerRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[\Override]
    protected function getRequestClass(): string
    {
        return KickPlayerRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getPlayerId', 'uid', '666', 666],
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getPlayeRId'],
        ];
    }
}
