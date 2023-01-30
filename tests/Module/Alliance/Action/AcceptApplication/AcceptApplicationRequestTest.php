<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use MPScholten\RequestParser\NotFoundException;
use Stu\RequestTestCase;

/**
 * @extends RequestTestCase<AcceptApplicationRequest>
 */
class AcceptApplicationRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return AcceptApplicationRequest::class;
    }

    public function testGetApplicationIdErrorsIfNotSet(): void
    {
        static::expectException(NotFoundException::class);

        $this->buildRequest()->getApplicationId();
    }

    public function testGetApplicationIdReturnsValue(): void
    {
        $value = 666;

        $_GET['aid'] = (string) $value;

        static::assertSame(
            $value,
            $this->buildRequest()->getApplicationId()
        );
    }
}
