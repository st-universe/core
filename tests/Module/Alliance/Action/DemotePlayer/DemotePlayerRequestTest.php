<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DemotePlayerRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return DemotePlayerRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getPlayerId', 'uid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getPlayerId'],
        ];
    }
}
