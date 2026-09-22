<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use request;
use Stu\StuTestCase;

class AddKnPostRequestTest extends StuTestCase
{
    public function testGetTextKeepsAngleBrackets(): void
    {
        $text = 'Vorher << danach >>';
        request::setMockVars(['text' => $text]);

        $request = new AddKnPostRequest();

        $this->assertSame($text, $request->getText());
    }
}
