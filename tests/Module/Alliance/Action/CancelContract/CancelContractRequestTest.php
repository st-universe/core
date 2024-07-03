<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class CancelContractRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return CancelContractRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getRelationId', 'al', '666', 666],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getRelationId'],
        ];
    }
}
