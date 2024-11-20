<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class RenameBoardRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return RenameBoardRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getBoardId', 'bid', '666', 666],
            ['getTitle', 'tname', '<foo>bar</foo>', 'bar'],
            ['getTitle', 'tname', null, ''],
        ];
    }

    #[Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
        ];
    }
}
