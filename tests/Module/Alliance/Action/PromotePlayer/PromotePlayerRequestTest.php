<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class PromotePlayerRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return PromotePlayerRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getPlayerId', 'uid', '666', 666],
            ['getPromotionType', 'type', '666', 666],
        ];
    }

    #[Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getPlayerId'],
            ['getPromotionType'],
        ];
    }
}
