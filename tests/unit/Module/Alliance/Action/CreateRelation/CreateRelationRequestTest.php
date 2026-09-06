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

    #[\Override]
    protected function getRequestClass(): string
    {
        return CreateRelationRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getRelationType',  'type', '666', 666],
            ['getCounterpartId', 'oid',  '666', 666]
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getRelationType'],
            ['getCounterpartId']
        ];
    }

    public function testGetPermissionsIncludesSelectedCheckboxes(): void
    {
        $_GET = [
            'relation_permissions' => 0,
            'relation_permission_1' => 1,
            'relation_permission_2' => 2
        ];

        self::assertSame(3, $this->buildRequest()->getPermissions());
    }
}
