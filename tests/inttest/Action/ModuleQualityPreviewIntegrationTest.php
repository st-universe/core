<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMXPath;
use Override;
use Stu\ActionTestCase;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEnergyWeapon;
use Stu\Lib\ModuleScreen\ModuleSelectorEntryInterface;
use Stu\Lib\ModuleScreen\ModuleSelectorInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactory;
use Stu\Module\Twig\TwigHelper;
use Stu\Module\Twig\TwigPage;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Twig\Environment;

final class ModuleQualityPreviewIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Colony $colony;
    private SpacecraftRump $rump;
    private Module $module;
    private ModuleSelectorInterface $selector;
    private ModuleSelectorEntryInterface $entry;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->colony = $this->em->find(Colony::class, 42);
        $this->rump = $this->em->find(Ship::class, 42)->getRump();
        $this->selector = $this->selector();
        $entry = array_find($this->selector->getAvailableModules(), static fn (ModuleSelectorEntryInterface $item): bool => $item->getModule()->getisNpc() !== true);
        self::assertNotNull($entry);
        $this->entry = $entry;
        $this->module = $entry->getModule();
        $this->rump->getBaseValues()->setBaseDamage(25)->setModuleLevel($this->module->getLevel());
        $this->module->setDefaultFactor(0);
        $this->colony->getChangeable()->setMaxStorage(10000);
        $this->getContainer()->get(StorageManagerInterface::class)->addStorage($this->colony, $this->module->getCommodity(), [0 => 2, 20 => 2, 70 => 2]);
        $this->em->flush();
    }

    private function selector(?SpacecraftBuildplan $plan = null): ModuleSelectorInterface
    {
        $twigPage = $this->getContainer()->make(TwigPage::class, ['environment' => $this->twig()]);
        return $this->getContainer()->make(ColonyLibFactory::class, ['twigPage' => $twigPage])->createModuleSelector(SpacecraftModuleTypeEnum::PHASER, $this->colony, $this->rump, $this->colony->getUser(), $plan);
    }

    private function twig(): Environment
    {
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        return $twig;
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML('<meta charset="utf-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        return new DOMXPath($document);
    }

    public function testWarehouseQualityPreviewsUseTheSameFinalRoundingAsBuiltShips(): void
    {
        self::assertSame(25, $this->entry->getValue());
        self::assertSame(25, $this->entry->forQuality(19)->getValue());
        self::assertSame(26, $this->entry->forQuality(20)->getValue());
        self::assertSame(27, $this->entry->forQuality(70)->getValue());
        self::assertSame(25, $this->entry->getValue());
        self::assertContains('+7,0%', array_column($this->entry->getQualityVariants(), 'label'));
    }

    public function testSelectorRendersAllQualityValuesAndKeepsOnlyCurrentValuesInTheTable(): void
    {
        $html = $this->twig()->render('html/ship/construction/moduleSelector/selector.twig', ['SELECTOR' => $this->selector]);
        $xpath = $this->xpath($html);
        $id = $this->module->getId();
        $current = $xpath->query('//*[@id="' . $id . '_content"]')->item(0);
        self::assertNotNull($current);
        self::assertStringContainsString('Basisschaden (abstrakt): 25', preg_replace('/\s+/u', ' ', trim($current->textContent)));
        foreach ([0 => 25, 20 => 26, 70 => 27] as $steps => $expected) {
            $preview = $xpath->query('//template[@data-module-id="' . $id . '" and @data-module-quality="' . $steps . '"]')->item(0);
            self::assertNotNull($preview);
            self::assertStringContainsString('Basisschaden (abstrakt): ' . $expected, preg_replace('/\s+/u', ' ', trim($preview->textContent)));
            self::assertSame(0, $xpath->query('.//*[@id]', $preview)->length);
        }
        self::assertSame(1, $xpath->query('//select[@name="module_quality[' . $id . ']"]')->length);
    }

    public function testExistingBuildplanShowsItsStoredBonusInBothPlacesWithoutMutation(): void
    {
        $plan = new SpacecraftBuildplan()->setUser($this->colony->getUser())->setRump($this->rump)->setName('Qualitätsvorschau')->setBuildtime(0)->setCrew(1)->setSignature(md5('preview'));
        $slot = new BuildplanModule()->setBuildplan($plan)->setModule($this->module)->setModuleType(SpacecraftModuleTypeEnum::PHASER)->setImprovementSteps(20);
        $plan->getModules()->add($slot);
        $this->em->persist($plan);
        $this->em->persist($slot);
        $this->em->flush();
        $selector = $this->selector($plan);
        $html = $this->twig()->render('html/ship/construction/moduleScreen.twig', ['COLONY' => $this->colony, 'RUMP' => $this->rump, 'PLAN' => $plan, 'SHIP' => null, 'MODULE_SELECTORS' => [$selector], 'MAX_CREW_COUNT' => 100, 'SESSIONSTRING' => 'session']);
        $xpath = $this->xpath($html);
        foreach ([$this->module->getId() . '_content', 'module_type_7'] as $id) {
            $node = $xpath->query('//*[@id="' . $id . '"]')->item(0);
            self::assertNotNull($node);
            self::assertStringContainsString('Basisschaden (abstrakt): 26', preg_replace('/\s+/u', ' ', trim($node->textContent)));
        }
        self::assertSame(1, $xpath->query('//select[@name="module_quality[' . $this->module->getId() . ']"]/option[@value="20" and @selected]')->length);
        $this->em->refresh($slot);
        self::assertSame(20, $slot->getImprovementSteps());
        self::assertSame(26, new ModuleRumpWrapperEnergyWeapon($this->rump, $plan)->getValue());
    }
}
