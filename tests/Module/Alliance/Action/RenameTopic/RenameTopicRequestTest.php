<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class RenameTopicRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return RenameTopicRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getTopicId', 'tid', '666', 666],
            ['getTitle', 'tname', '<foo>bar</foo>', 'bar'],
            ['getTitle', 'tname', null, ''],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getTopicId'],
        ];
    }
}
