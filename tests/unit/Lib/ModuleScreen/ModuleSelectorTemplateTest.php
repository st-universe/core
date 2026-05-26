<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ModuleSelectorTemplateTest extends StuTestCase
{
    public function testRenderMarksEmptySlotAsChecked(): void
    {
        $selector = $this->mock(ModuleSelectorInterface::class);
        $entry = $this->mock(ModuleSelectorEntryInterface::class);
        $module = $this->mock(Module::class);
        $rump = $this->mock(SpacecraftRump::class);

        $selector->shouldReceive('getRump')
            ->withNoArgs()
            ->andReturn($rump);
        $selector->shouldReceive('getModuleType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::HULL);
        $selector->shouldReceive('allowMultiple')
            ->withNoArgs()
            ->andReturn(false);
        $selector->shouldReceive('getModuleTypeLevel')
            ->withNoArgs()
            ->andReturn(1);
        $selector->shouldReceive('getAvailableModules')
            ->withNoArgs()
            ->andReturn([$entry]);
        $selector->shouldReceive('allowEmptySlot')
            ->withNoArgs()
            ->andReturn(true);
        $selector->shouldReceive('isEmptySlot')
            ->withNoArgs()
            ->andReturn(true);

        $entry->shouldReceive('getModule')
            ->withNoArgs()
            ->andReturn($module);
        $entry->shouldReceive('isChosen')
            ->withNoArgs()
            ->andReturn(false);
        $entry->shouldReceive('getModuleLevelClass')
            ->withNoArgs()
            ->andReturn('');
        $entry->shouldReceive('getNeededCrew')
            ->withNoArgs()
            ->andReturn(1);
        $entry->shouldReceive('getStoredAmount')
            ->withNoArgs()
            ->andReturn(1);
        $entry->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn(100);
        $entry->shouldReceive('getAddonValues')
            ->withNoArgs()
            ->andReturn([]);

        $module->shouldReceive('getisNpc')
            ->withNoArgs()
            ->andReturn(false);
        $module->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(101);
        $module->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn(101);
        $module->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Hüllenmodul');
        $module->shouldReceive('getDescription')
            ->withNoArgs()
            ->andReturn('Hüllenmodul');

        $twig = new Environment(new FilesystemLoader('src'));

        $result = $twig->render('html/ship/construction/moduleSelector/selector.twig', [
            'SELECTOR' => $selector
        ]);

        $this->assertStringContainsString(
            '<input type="radio" checked="true" name="mod_1[]" value="0"',
            $result
        );
    }
}
