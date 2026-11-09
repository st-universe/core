<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Doctrine\Common\Collections\ArrayCollection;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class ModuleQualitySelectorTemplateTest extends StuTestCase
{
    /** @return iterable<string, array{bool, bool}> */
    public static function moduleKinds(): iterable
    {
        yield 'normal weapon' => [false, false];
        yield 'npc weapon' => [true, false];
        yield 'normal special module' => [false, true];
        yield 'npc special module' => [true, true];
    }

    #[DataProvider('moduleKinds')]
    public function testQualityChoicesPreserveSelectionAndExposeOnlyIdFreeStatistics(bool $npc, bool $special): void
    {
        $type = $special ? SpacecraftModuleTypeEnum::SPECIAL : SpacecraftModuleTypeEnum::PHASER;
        $selector = $this->mock(ModuleSelectorInterface::class);
        $selector->shouldReceive('getRump')->andReturn($this->mock(SpacecraftRump::class));
        $selector->shouldReceive('getModuleType')->andReturn($type);
        $selector->shouldReceive('getModuleTypeLevel')->andReturn(1);
        $selector->shouldReceive('allowMultiple')->andReturn($special);
        $selector->shouldReceive('allowEmptySlot')->andReturn(false);
        $entry = $this->mock(ModuleSelectorEntryInterface::class);
        $entry->shouldReceive('getSelectedQuality')->andReturn(20);
        $entry->shouldReceive('getQualityVariants')->andReturn([
            ['steps' => 0, 'amount' => 2, 'label' => 'Standard'],
            ['steps' => 20, 'amount' => 0, 'label' => '+2,0%'],
            ['steps' => 70, 'amount' => 3, 'label' => '+7,0%']
        ]);
        $entry->shouldReceive('isChosen')->andReturn(true);
        $entry->shouldReceive('getNeededCrew')->andReturn(1);
        $entry->shouldReceive('getStoredAmount')->andReturn(5);
        $entry->shouldReceive('getModuleLevelClass')->andReturn('');
        $entry->shouldNotReceive('getValue');
        $module = $this->mock(Module::class);
        $entry->shouldReceive('getModule')->andReturn($module);
        $module->shouldReceive('getisNpc')->andReturn($npc);
        $module->shouldReceive('getId', 'getCommodityId')->andReturn(101);
        $module->shouldReceive('getName', 'getDescription')->andReturn('Testmodul');
        $module->shouldReceive('getSpecials')->andReturn(new ArrayCollection());
        foreach ([0 => 25, 20 => 26, 70 => 27] as $steps => $value) {
            $preview = $this->mock(ModuleSelectorEntryInterface::class);
            $preview->shouldReceive('getValue')->andReturn($value);
            $entry->shouldReceive('forQuality')->with($steps)->andReturn($preview);
        }
        $selector->shouldReceive('getAvailableModules')->andReturn([$entry]);
        $twig = new Environment(new FilesystemLoader('src'), ['strict_variables' => true]);
        $html = $twig->render('html/ship/construction/moduleSelector/selector.twig', ['SELECTOR' => $selector]);
        $document = new DOMDocument();
        $document->loadHTML('<meta charset="utf-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);
        self::assertSame(1, $xpath->query('//select[@id="module_quality_101"]/option[@value="20" and @selected]')->length);
        self::assertSame('0', trim($xpath->query('//*[@id="module_101_stock"]')->item(0)->textContent));
        self::assertSame(1, $xpath->query('//input[@id="module_101_selection" and @checked and @type="' . ($special ? 'checkbox' : 'radio') . '"]')->length);
        self::assertSame(0, $xpath->query('//*[@id="101_content"]//*[@id]')->length);
        self::assertSame(0, $xpath->query('//template//*[@id]')->length);
        self::assertSame(3, $xpath->query('//template[@data-module-id="101"]')->length);
        self::assertStringContainsString('updateSelectedModuleQuality(this, ' . $type->value . ', 101', $html);
        self::assertStringContainsString('+2,0%', $html);
        if (!$special) {
            self::assertStringContainsString('26', $xpath->query('//*[@id="101_content"]')->item(0)->textContent);
            self::assertStringContainsString('25', $xpath->query('//template[@data-module-quality="0"]')->item(0)->textContent);
            self::assertStringContainsString('27', $xpath->query('//template[@data-module-quality="70"]')->item(0)->textContent);
        }
        if ($npc) {
            self::assertStringContainsString('NPC Module', $html);
        }
    }
}
