<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class StorageQualityDetailsTest extends StuTestCase
{
    public function testColonyQuantityExpandsTheCompactBreakdownUnderTheGoodsLine(): void
    {
        $storage = $this->storage(10601, 'Sensoren', 10, [4 => 3, 17 => 5]);
        $html = $this->colony([$storage]);
        $xpath = $this->xpath($html);
        self::assertSame(1, $xpath->query('//details')->length);
        self::assertSame('td', $xpath->query('//details')->item(0)->parentNode->nodeName);
        self::assertSame('Lagerbestand Sensoren: 10', $xpath->query('//summary')->item(0)->getAttribute('aria-label'));
        self::assertSame(1, $xpath->query('//summary//a[contains(@onclick, "getCommodityLocations(this, 10601)")]')->length);
        self::assertSame(1, $xpath->query('//summary//span[contains(@style, "text-decoration: underline") and normalize-space(.)="10"]')->length);
        self::assertSame('2 × Standard, 3 × +0,4 %, 5 × +1,7 %', $xpath->query('//details/div')->item(0)->textContent);
        self::assertSame(1, $xpath->query('//tr[@class="row"]/td[2][contains(@style,"vertical-align: top")]')->length);
        self::assertSame(0, $xpath->query('//select')->length);
    }

    /** @return iterable<string, array{array<int, int>, string}> */
    public static function singlePositiveQuality(): iterable
    {
        yield 'one improved tier without standard stock' => [[4 => 2], '2 × +0,4 %'];
        yield 'one improved item mixed with standard' => [[17 => 1], '1 × Standard, 1 × +1,7 %'];
    }

    #[DataProvider('singlePositiveQuality')]
    public function testOneImprovedTierIsEnoughToMakeTheAmountExpandable(array $qualities, string $description): void
    {
        $xpath = $this->xpath($this->colony([$this->storage(10601, 'Sensoren', 2, $qualities)]));
        self::assertSame(1, $xpath->query('//details')->length);
        self::assertSame($description, $xpath->query('//details/div')->item(0)->textContent);
    }

    public function testStandardOnlyStocksStayNonInteractiveAndKeepTheColonyIconLink(): void
    {
        $storage = $this->storage(10601, 'Sensoren', 4);
        $colony = $this->xpath($this->colony([$storage]));
        self::assertSame(0, $colony->query('//details | //summary | //select')->length);
        self::assertSame(1, $colony->query('//a[contains(@onclick,"getCommodityLocations(this, 10601)")]')->length);
        self::assertSame(1, $colony->query('//tr[@class="row"]/td[2][contains(@style,"vertical-align: middle")]')->length);
        $ship = $this->xpath($this->spacecraft([$storage]));
        self::assertSame(0, $ship->query('//details | //summary | //select')->length);
    }

    public function testShipKeepsEachBreakdownInItsOwnCellWithinTheTwoColumnGrid(): void
    {
        $html = $this->spacecraft([
            $this->storage(10601, 'Sensoren', 10, [4 => 3, 17 => 5]),
            $this->storage(2, 'Duranium', 9),
            $this->storage(10901, 'Warpantrieb', 3, [1 => 3]),
            $this->storage(3, 'Deuterium', 8)
        ]);
        $xpath = $this->xpath($html);
        $grid = $xpath->query('//div[@class="spacecraft-storage-items"]')->item(0);
        self::assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $grid->getAttribute('style'));
        $children = [];
        foreach ($grid->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $child->nodeName;
                if ($child->nodeName === 'details') {
                    $child->setAttribute('open', '');
                }
            }
        }
        self::assertSame(['details', 'span', 'details', 'span'], $children);
        self::assertSame(2, $xpath->query('//div[@class="spacecraft-storage-items"]/details[@open]')->length);
        self::assertSame(1, $xpath->query('//details[1]/div[contains(@class,"storage-quality-breakdown")]')->length);
        self::assertStringNotContainsString('float:', $html);
        self::assertSame(0, $xpath->query('//a | //select')->length);
    }

    /** @param list<Storage> $storage */
    private function colony(array $storage): string
    {
        return $this->twig()->render('html/colony/component/colonyStorage.twig', [
            'STORAGE_SUM' => 10, 'MAX_STORAGE' => 100, 'STORAGE_SUM_PERCENT' => 10, 'PRODUCTION_SUM' => 0,
            'STORAGE' => array_map(static fn (Storage $item): array => ['commodity' => $item->getCommodity(), 'storage' => $item, 'production' => null], $storage)
        ]);
    }

    /** @param list<Storage> $storage */
    private function spacecraft(array $storage): string
    {
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('getStorage')->andReturn(new ArrayCollection($storage));
        $ship->shouldReceive('getStorageSum')->andReturn(30);
        $ship->shouldReceive('getMaxStorage')->andReturn(100);
        return $this->twig()->render('html/spacecraft/spacecraftStorage.twig', ['SPACECRAFT' => $ship]);
    }

    private function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 4) . '/src'), ['strict_variables' => true]);
        $twig->addFilter(new TwigFilter('formatProductionValue', static fn (int $value): string => (string) $value));
        return $twig;
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML('<meta charset="utf-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        return new DOMXPath($document);
    }

    /** @param array<int, int> $qualities */
    private function storage(int $id, string $name, int $amount, array $qualities = []): Storage
    {
        $commodity = $this->mock(Commodity::class);
        $commodity->shouldReceive('getId')->andReturn($id);
        $commodity->shouldReceive('getName')->andReturn($name);
        return new Storage()->setCommodity($commodity)->setAmount($amount)->addModuleQualityAmounts($qualities);
    }
}
