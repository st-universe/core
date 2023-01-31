<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DeleteTopicRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return DeleteTopicRequest::class;
    }

    public function requestVarsDataProvider(): array
    {
        return [
            ['getTopicId', 'tid', '666', 666],
        ];
    }

    public function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getTopicId'],
        ];
    }
}
