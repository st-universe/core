<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<CreateRelationRequest>
 */
class CreateRelationRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return CreateRelationRequest::class;
    }

    public function requestVarsDataProvider(): array
    {
        return [
            ['getRelationType', 'type', '666', 666],
            ['getCounterpartId', 'oid', '666', 666],
        ];
    }

    public function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getRelationType'],
            ['getCounterpartId'],
        ];
    }
}
