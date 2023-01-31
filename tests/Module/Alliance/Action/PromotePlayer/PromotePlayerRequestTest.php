<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class PromotePlayerRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return PromotePlayerRequest::class;
    }

    public function requestVarsDataProvider(): array
    {
        return [
            ['getPlayerId', 'uid', '666', 666],
            ['getPromotionType', 'type', '666', 666],
        ];
    }

    public function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getPlayerId'],
            ['getPromotionType'],
        ];
    }
}
