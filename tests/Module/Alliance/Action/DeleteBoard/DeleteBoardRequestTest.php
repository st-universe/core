<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DeleteBoardRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return DeleteBoardRequest::class;
    }

    public static function requestVarsDataProvider(): array
    {
        return [
            ['getBoardId', 'bid', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getBoardId'],
        ];
    }
}
