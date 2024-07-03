<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class SetTopicStickyRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return SetTopicStickyRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getTopicId', 'tid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getTopicId'],
        ];
    }
}
