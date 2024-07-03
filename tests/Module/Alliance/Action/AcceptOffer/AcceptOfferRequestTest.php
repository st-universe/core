<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptOffer;

use Override;
use Stu\RequestTestCase;
use Stu\RequiredRequestTestCaseTrait;

/**
 * @extends RequestTestCase<AcceptOfferRequest>
 */
class AcceptOfferRequestTest extends RequestTestCase
{
    use RequiredRequestTestCaseTrait;

    #[Override]
    protected function getRequestClass(): string
    {
        return AcceptOfferRequest::class;
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
