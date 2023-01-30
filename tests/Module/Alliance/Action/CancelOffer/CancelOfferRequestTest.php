<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

class CancelOfferRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    protected function getRequestClass(): string
    {
        return CancelOfferRequest::class;
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
