<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Stu\StuTestCase;

class ReponseFactoryTest extends StuTestCase
{

    /**
     * @var null|ReponseFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new ReponseFactory();
    }

    public function testCreateResponseReturnsResponse(): void
    {
        $statusCode = 404;
        $reason = 'some-reason';

        $result = $this->factory->createResponse($statusCode, $reason);

        $this->assertSame(
            $statusCode,
            $result->getStatusCode()
        );
        $this->assertSame(
            $reason,
            $result->getReasonPhrase()
        );
    }
}
