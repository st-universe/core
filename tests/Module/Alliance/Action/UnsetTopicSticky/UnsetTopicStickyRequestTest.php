<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class UnsetTopicStickyRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return UnsetTopicStickyRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getTopicId', 'tid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getTopIcId'],
        ];
    }
}
