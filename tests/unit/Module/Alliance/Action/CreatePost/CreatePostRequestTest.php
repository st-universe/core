<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<CreatePostRequest>
 */
class CreatePostRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[\Override]
    protected function getRequestClass(): string
    {
        return CreatePostRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getText', 'ttext', '<foo>bar</foo>', 'bar'],
            ['getText', 'ttext', null, ''],
            ['getTopicId', 'topicid', '666', 666],
            ['getBoardId', 'boardid', '666', 666],
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
            ['getTopicId'],
        ];
    }
}
