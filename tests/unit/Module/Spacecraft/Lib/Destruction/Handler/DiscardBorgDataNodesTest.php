<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

final class DiscardBorgDataNodesTest extends StuTestCase
{
    /** @return iterable<string, array{bool}> */
    public static function hydrationStates(): iterable
    {
        yield 'fresh storage with no scalar commodity ID or commodity index' => [false];
        yield 'hydrated storage indexed by commodity ID' => [true];
    }

    #[DataProvider('hydrationStates')]
    public function testOnlyDataNodesAreDeletedAndCannotBeProcessedAgain(bool $hydrated): void
    {
        $ship = new Ship();
        $nodes = $this->storage(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE, 4);
        $nanites = $this->storage(76, 12);
        $material = $this->storage(2, 50);
        if ($hydrated) {
            new ReflectionProperty(Storage::class, 'commodity_id')->setValue($nodes, CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
            $ship->getStorage()->set(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE, $nodes);
        } else {
            self::assertSame(0, $nodes->getCommodityId());
            $ship->getStorage()->add($nodes);
        }
        $ship->getStorage()->set(76, $nanites);
        $ship->getStorage()->set(2, $material);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $repository = $this->mock(StorageRepositoryInterface::class);
        $repository->shouldReceive('delete')->once()->with($nodes);
        $subject = new DiscardBorgDataNodes($repository);
        $information = new InformationWrapper();

        $subject->handleSpacecraftDestruction(null, $wrapper, SpacecraftDestructionCauseEnum::SHIP_FIGHT, $information);
        $subject->handleSpacecraftDestruction(null, $wrapper, SpacecraftDestructionCauseEnum::SHIP_FIGHT, $information);

        self::assertSame([$nanites, $material], array_values($ship->getStorage()->toArray()));
        self::assertSame(12, $nanites->getAmount());
        self::assertSame(50, $material->getAmount());
        self::assertSame([], $information->getInformations());
    }

    public function testCargoWithoutDataNodesDoesNotDeleteAnything(): void
    {
        $ship = new Ship();
        $cargo = $this->storage(76, 5);
        $ship->getStorage()->set(76, $cargo);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $repository = $this->mock(StorageRepositoryInterface::class);
        $repository->shouldNotReceive('delete');

        new DiscardBorgDataNodes($repository)->handleSpacecraftDestruction(null, $wrapper, SpacecraftDestructionCauseEnum::SELF_DESTRUCTION, new InformationWrapper());

        self::assertSame($cargo, $ship->getStorage()->get(76));
    }

    private function storage(int $commodityId, int $amount): Storage
    {
        $commodity = new Commodity();
        new ReflectionProperty(Commodity::class, 'id')->setValue($commodity, $commodityId);
        return new Storage()->setCommodity($commodity)->setAmount($amount);
    }
}
