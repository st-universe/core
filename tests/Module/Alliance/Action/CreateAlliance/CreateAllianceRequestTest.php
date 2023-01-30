<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use MPScholten\RequestParser\NotFoundException;
use Stu\RequestTestCase;

/**
 * @extends RequestTestCase<CreateAllianceRequest>
 */
class CreateAllianceRequestTest extends RequestTestCase
{
    protected function getRequestClass(): string
    {
        return CreateAllianceRequest::class;
    }


    public function testGetFactionModeReturnsDefaultIfNotSet(): void
    {
        static::assertSame(
            0,
            $this->buildRequest()->getFactionMode()
        );
    }

    public function testGetFactionModeReturnsValue(): void
    {
        $value = 666;

        $_GET['factionid'] = (string) $value;

        static::assertSame(
            $value,
            $this->buildRequest()->getFactionMode()
        );
    }

    public function testGetNameErrorsIfNotSet(): void
    {
        static::expectException(NotFoundException::class);

        $this->buildRequest()->getName();
    }

    public function testGetNameReturnsSanitizedString(): void
    {
        $value = 'some-name';

        $_GET['name'] = sprintf('<foo>%s</foo>', $value);

        static::assertSame(
            $value,
            $this->buildRequest()->getName()
        );
    }

    public function testGetDescriptionErrorsIfNotSet(): void
    {
        static::expectException(NotFoundException::class);

        $this->buildRequest()->getDescription();
    }

    public function testGetDescriptionReturnsSanitizedString(): void
    {
        $value = 'some-description';

        $_GET['description'] = sprintf('<foo>%s</foo>', $value);

        static::assertSame(
            $value,
            $this->buildRequest()->getDescription()
        );
    }
}
