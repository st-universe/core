<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware\Response;

use Stu\StuTestCase;

class JsonResponseTest extends StuTestCase
{
    /**
     * @var null|JsonResponse
     */
    private $response;

    public function setUp(): void
    {
        $this->response = new JsonResponse();
    }

    public function testWithDataWritesData(): void
    {
        $data = ['some' => 'data'];

        $this->assertSame(
            $this->response,
            $this->response->withData($data)
        );
    }

    public function testWithErrorWritesData(): void
    {
        $errorCode = 666;
        $errorMessage = 'number of the beast';

        $this->assertSame(
            $this->response,
            $this->response->withError($errorCode, $errorMessage)
        );
    }
}
