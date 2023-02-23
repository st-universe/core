<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class RenameBoardRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return RenameBoardRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getBoardId', 'bid', '666', 666],
            ['getTitle', 'tname', '<foo>bar</foo>', 'bar'],
            ['getTitle', 'tname', null, ''],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
        ];
    }
}
