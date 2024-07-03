<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<CreateAllianceRequest>
 */
class CreateAllianceRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return CreateAllianceRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getDescription', 'description', '<foo>bar</foo>', 'bar'],
            ['getName', 'name', '<foo>bar</foo>', 'bar'],
            ['getFactionMode', 'factionid', '666', 666],
            ['getFactionMode', 'factionid', null, 0],
        ];
    }

    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getDescription'],
            ['getName'],
        ];
    }
}
