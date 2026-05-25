<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Mockery\MockInterface;
use Stu\StuTestCase;

class ModuleScreenTabTest extends StuTestCase
{
    private MockInterface&ModuleSelectorInterface $moduleSelector;
    private MockInterface&ModuleSelectorEntryInterface $selectedEntry;

    private ModuleScreenTab $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleSelector = $this->mock(ModuleSelectorInterface::class);
        $this->selectedEntry = $this->mock(ModuleSelectorEntryInterface::class);

        $this->subject = new ModuleScreenTab($this->moduleSelector);
    }

    public function testGetCssClassMarksMandatorySelectedUnavailableModuleAsUnselected(): void
    {
        $this->moduleSelector->shouldReceive('getAvailableModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);
        $this->moduleSelector->shouldReceive('hasSelectedModule')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('allowEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->moduleSelector->shouldReceive('isMandatory')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('getSelectedModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);

        $this->selectedEntry->shouldReceive('isDisabled')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->assertSame(
            'module_selector module_selector_unselected',
            $this->subject->getCssClass()
        );
    }

    public function testGetCssClassDoesNotMarkSelectedAvailableModuleAsUnselected(): void
    {
        $this->moduleSelector->shouldReceive('getAvailableModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);
        $this->moduleSelector->shouldReceive('hasSelectedModule')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('allowEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->moduleSelector->shouldReceive('isMandatory')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('getSelectedModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);

        $this->selectedEntry->shouldReceive('isDisabled')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->assertSame(
            'module_selector',
            $this->subject->getCssClass()
        );
    }

    public function testGetCssClassMarksOptionalSelectedUnavailableModuleAsUnselected(): void
    {
        $this->moduleSelector->shouldReceive('getAvailableModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);
        $this->moduleSelector->shouldReceive('hasSelectedModule')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('allowEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('isEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->moduleSelector->shouldReceive('isMandatory')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->moduleSelector->shouldReceive('getSelectedModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);

        $this->selectedEntry->shouldReceive('isDisabled')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->assertSame(
            'module_selector module_selector_unselected',
            $this->subject->getCssClass()
        );
    }

    public function testGetCssClassKeepsEmptyOptionalSlotSkipped(): void
    {
        $this->moduleSelector->shouldReceive('getAvailableModules')
            ->withNoArgs()
            ->once()
            ->andReturn([$this->selectedEntry]);
        $this->moduleSelector->shouldReceive('hasSelectedModule')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->moduleSelector->shouldReceive('allowEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('isEmptySlot')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->moduleSelector->shouldReceive('isMandatory')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->assertSame(
            'module_selector module_selector_skipped',
            $this->subject->getCssClass()
        );
    }
}
