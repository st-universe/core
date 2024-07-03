<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SuggestPeace;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class SuggestPeaceRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return SuggestPeaceRequest::class;
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
