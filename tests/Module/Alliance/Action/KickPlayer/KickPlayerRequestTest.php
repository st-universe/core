<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class KickPlayerRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return KickPlayerRequest::class;
    }

    public function requestVarsDataProvider(): array
    {
        return [
            ['getPlayerId', 'uid', '666', 666],
        ];
    }

    public function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getPlayeRId']
        ];
    }
}
