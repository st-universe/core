<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use request;
use Stu\StuTestCase;

class EditKnPostRequestTest extends StuTestCase
{
    public function testGetTextKeepsAngleBrackets(): void
    {
        $text = 'Vorher << danach >>';
        request::setMockVars(['text' => $text]);

        $request = new EditKnPostRequest();

        $this->assertSame($text, $request->getText());
    }
}
