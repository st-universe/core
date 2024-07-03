<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class DeleteBoardRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return DeleteBoardRequest::class;
    }

    #[Override]
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
