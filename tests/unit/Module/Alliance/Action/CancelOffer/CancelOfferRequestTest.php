<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class CancelOfferRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return CancelOfferRequest::class;
    }

    #[Override]
    public static function requestVarsDataProvider(): array
    {
        return [
            ['getRelationId', 'al', '666', 666],
        ];
    }

    #[Override]
    public static function requiredRequestVarsDataProvider(): array
    {
        return [
            ['getRelationId'],
        ];
    }
}
