<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<CreatePostRequest>
 */
class CreatePostRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return CreatePostRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getText', 'ttext', '<foo>bar</foo>', 'bar'],
            ['getText', 'ttext', null, ''],
            ['getTopicId', 'tid', '666', 666],
            ['getBoardId', 'bid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
            ['getTopicId'],
        ];
    }
}
