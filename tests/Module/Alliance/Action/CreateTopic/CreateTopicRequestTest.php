<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateTopic;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<CreateTopicRequest>
 */
class CreateTopicRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return CreateTopicRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getBoardId', 'bid', (string) 666, 666],
            ['getTopicTitle', 'tname', '<foo>bar</foo>', 'bar'],
            ['getText', 'ttext', '<foo>bar</foo>', 'bar'],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
        ];
    }
}
