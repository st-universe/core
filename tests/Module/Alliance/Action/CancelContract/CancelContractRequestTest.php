<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class CancelContractRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return CancelContractRequest::class;
    }

    public function requestVarsDataProvider(): array
    {
        return [
            ['getRelationId', 'al', '666', 666],
        ];
    }

    public function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getRelationId'],
        ];
    }
}
