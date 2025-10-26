<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\StuTestCase;

class JavascriptExecutionTest extends StuTestCase
{
    private JavascriptExecutionInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->subject = new JavascriptExecution();
    }

    public function testGetExecuteJS(): void
    {
        $this->assertNull($this->subject->getExecuteJS(JavascriptExecutionTypeEnum::AFTER_RENDER));
        $this->assertNull($this->subject->getExecuteJS(JavascriptExecutionTypeEnum::BEFORE_RENDER));
        $this->assertNull($this->subject->getExecuteJS(JavascriptExecutionTypeEnum::ON_AJAX_UPDATE));
    }

    public function testAddExecuteJSafterRender(): void
    {
        $this->subject->addExecuteJS('js();', JavascriptExecutionTypeEnum::AFTER_RENDER);
        $result = $this->subject->getExecuteJS(JavascriptExecutionTypeEnum::AFTER_RENDER);

        $this->assertEquals(['js();'], $result);
    }

    public function testAddExecuteJSbeforeRender(): void
    {
        $this->subject->addExecuteJS('js();', JavascriptExecutionTypeEnum::BEFORE_RENDER);
        $result = $this->subject->getExecuteJS(JavascriptExecutionTypeEnum::BEFORE_RENDER);

        $this->assertEquals(['js();'], $result);
    }

    public function testAddExecuteJSonAjaxUpdate(): void
    {
        $this->subject->addExecuteJS('js();', JavascriptExecutionTypeEnum::ON_AJAX_UPDATE);
        $result = $this->subject->getExecuteJS(JavascriptExecutionTypeEnum::ON_AJAX_UPDATE);

        $this->assertEquals(['js();'], $result);
    }
}
