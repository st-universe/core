<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use MPScholten\RequestParser\NotFoundException;
use Stu\RequestTestCase;

class CancelOfferRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return CancelOfferRequest::class;
    }

    public function testGetRelationIdErrorsIfNotSet(): void
    {
        static::expectException(NotFoundException::class);

        $this->buildRequest()->getRelationId();
    }

    public function testGetRelationIdReturnsValue(): void
    {
        $value = 666;

        $_GET['al'] = (string) $value;

        static::assertSame(
            $value,
            $this->buildRequest()->getRelationId()
        );
    }
}
