<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Lib\View\Provider\MaindeskProvider;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class BorgDataNodeMaindeskIntegrationTest extends ActionTestCase
{
    /** @return iterable<string, array{int, int}> */
    public static function globalStock(): iterable
    {
        yield 'empty progress' => [0, 0];
        yield 'first data node is visible with zero rounded percent' => [1, 0];
        yield 'completed progress' => [200, 100];
    }

    #[DataProvider('globalStock')]
    public function testTheRealMaindeskProviderAndTwigPlaceTheGlobalProgressDirectlyBelowOnlinePlayers(int $amount, int $percent): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $user = $em->find(User::class, 101);
        $colony = $em->find(Colony::class, 42);
        self::assertNotNull($user);
        self::assertNotNull($colony);
        $commodity = $this->commodity($em);
        if ($amount > 0) {
            $em->persist(new Storage()->setCommodity($commodity)->setUser($user)->setEntity($colony)->setAmount($amount));
            $em->flush();
        }
        $variables = [];
        $context = $this->mock(ViewControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('setTemplateVar')->andReturnUsing(static function (string $name, mixed $value) use (&$variables): void {
            $variables[$name] = $value;
        });
        $container->get(MaindeskProvider::class)->setTemplateVariables($context);
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 3) . '/src'));
        $helper = $container->make(TwigHelper::class, ['environment' => $twig]);
        $helper->registerFiltersAndFunctions();
        $helper->registerGlobalVariables();

        $html = $twig->render('html/view/maindesk.twig', $variables);

        self::assertSame($percent, $variables['BORG_DATA_NODE_PROGRESS_PERCENT']);
        self::assertSame($amount > 0, $variables['BORG_DATA_NODE_PROGRESS_VISIBLE']);
        $xpath = $this->xpath($html);
        $onlineItems = $this->elements($xpath, '//*[@id="randomactivplayer"]');
        $progressItems = $this->elements($xpath, '//*[@id="borgdatanodeprogress"]');
        self::assertCount(1, $onlineItems);
        if ($amount === 0) {
            self::assertCount(0, $progressItems);
            return;
        }
        self::assertCount(1, $progressItems);
        $online = $onlineItems[0];
        $progress = $progressItems[0];
        self::assertSame($online->parentNode, $progress->parentNode);
        self::assertCount(1, $this->elements($xpath, '//*[@id="randomactivplayer"]/following-sibling::div[1][@id="borgdatanodeprogress"]'));
        self::assertCount(1, $this->elements($xpath, '//table[1]/tr/td[2]//*[@id="borgdatanodeprogress"]'));
        self::assertStringContainsString('Globaler Borg-Datenknoten-Fortschritt', $progress->textContent);
        self::assertStringContainsString($percent . '%', $progress->textContent);
        $images = $this->elements($xpath, '//*[@id="borgdatanodeprogress"]//img[@src="/assets/bars/balken.png"]');
        self::assertCount($percent === 0 ? 2 : 1, $images);
        $filled = $images[0];
        self::assertStringContainsString('background-color: #00aa00', $filled->getAttribute('style'));
        self::assertStringContainsString('width:' . ($percent === 0 ? 0 : 300) . 'px', $filled->getAttribute('style'));
    }

    private function commodity(EntityManagerInterface $em): Commodity
    {
        $commodity = $em->find(Commodity::class, CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
        if ($commodity !== null) {
            return $commodity;
        }
        $metadata = $em->getClassMetadata(Commodity::class);
        $type = $metadata->generatorType;
        $generator = $metadata->idGenerator;
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        try {
            $commodity = new Commodity()->setName('Borg-Datenknoten');
            $metadata->setFieldValue($commodity, 'id', CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
            $em->persist($commodity);
            $em->flush();
            return $commodity;
        } finally {
            $metadata->setIdGeneratorType($type);
            $metadata->setIdGenerator($generator);
        }
    }

    private function xpath(string $html): DOMXPath
    {
        self::assertNotEmpty($html);
        $errors = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML($html));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errors);
        }
    }

    /** @return list<DOMElement> */
    private function elements(DOMXPath $xpath, string $expression): array
    {
        $matches = $xpath->query($expression);
        self::assertNotFalse($matches);
        $elements = [];
        foreach ($matches as $element) {
            self::assertInstanceOf(DOMElement::class, $element);
            $elements[] = $element;
        }
        return $elements;
    }
}
