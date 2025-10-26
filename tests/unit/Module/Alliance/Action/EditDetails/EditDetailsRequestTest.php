<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class EditDetailsRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[\Override]
    protected function getRequestClass(): string
    {
        return EditDetailsRequest::class;
    }

    #[\Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getName', 'name', '<foo>bar</foo>', 'bar'],
            ['getHomepage', 'homepage', '<foo>url</foo>', 'url'],
            ['getHomepage', 'homepage', null, ''],
            ['getDescription', 'description', '<foo>description</foo>', 'description'],
            ['getDescription', 'description', null, ''],
            ['getFactionMode', 'factionid', '666', 666],
            ['getFactionMode', 'factionid', null, 0],
            ['getAcceptApplications', 'acceptapp', '666', 666],
            ['getAcceptApplications', 'acceptapp', null, 0],
            ['getRgbCode', 'rgb', '<foo>bar</foo>', 'bar'],
        ];
    }

    #[\Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getName'],
            ['getRgbCode'],
        ];
    }
}
