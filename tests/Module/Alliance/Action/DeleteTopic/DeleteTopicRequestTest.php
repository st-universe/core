<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DeleteTopicRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return DeleteTopicRequest::class;
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
