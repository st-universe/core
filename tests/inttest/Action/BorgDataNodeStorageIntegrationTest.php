<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\ActionTestCase;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BorgDataNodeStorageIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private StorageRepositoryInterface $storage;
    private Commodity $commodity;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->storage = $this->getContainer()->get(StorageRepositoryInterface::class);
        $this->commodity = new Commodity()->setName('Borg-Datenknoten für den Bestandstest');
        $this->em->persist($this->commodity);
        $this->em->flush();
    }

    public function testEmptyStorageReturnsZero(): void
    {
        self::assertSame(0, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));
    }

    public function testPlayerAmountsAreSummedOnceAcrossAllStorageLocationsAndExcludeNpcAndDebris(): void
    {
        $colony = $this->entry(101, 17)->setEntity($this->entity(Colony::class, 42));
        $ship = $this->entry(101, 23)->setEntity($this->entity(Ship::class, 42));
        $otherPlayer = $this->entry(102, 31)->setEntity($this->entity(Ship::class, 1021));
        $post = $this->entry(101, 41)->setTradePost($this->entity(TradePost::class, 2))
            ->setEntity($this->entity(Station::class, 43));
        $offer = $this->entity(TradeOffer::class, 1)->getStorage();
        $offer->setCommodity($this->commodity)->setAmount(47);
        $npc = $this->entry(19, 1000)->setEntity($this->entity(Ship::class, 79));
        $unowned = $this->entry(1, 1000);
        $debris = $this->entry(101, 1000)->setTrumfield($this->entity(Trumfield::class, 1));
        $this->em->flush();

        self::assertSame(159, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));
        self::assertSame(5, $this->em->getRepository(Storage::class)->count(['commodity' => $this->commodity, 'user' => $this->entity(User::class, 101)]));
        self::assertSame([17, 23, 31, 41, 47, 1000, 1000, 1000], array_map(static fn (Storage $entry): int => $entry->getAmount(), [$colony, $ship, $otherPlayer, $post, $offer, $npc, $unowned, $debris]));
    }

    public function testTransfersBetweenPlayersKeepTheTotalWhileLossesAndNpcOwnershipReduceIt(): void
    {
        $colony = $this->entry(101, 80)->setEntity($this->entity(Colony::class, 42));
        $ship = $this->entry(102, 40)->setEntity($this->entity(Ship::class, 1021));
        $this->em->flush();
        self::assertSame(120, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));

        $colony->setAmount(60);
        $ship->setAmount(60);
        $this->em->flush();
        self::assertSame(120, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));

        $ship->setUser($this->entity(User::class, 19));
        $this->em->flush();
        self::assertSame(60, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));

        $colony->setAmount(40);
        $this->em->flush();
        self::assertSame(40, $this->storage->getCommodityAmountInPlayerStorage($this->commodity->getId()));
    }

    private function entry(int $owner, int $amount): Storage
    {
        $entry = new Storage()->setCommodity($this->commodity)->setUser($this->entity(User::class, $owner))->setAmount($amount);
        $this->em->persist($entry);
        return $entry;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function entity(string $class, int $id): object
    {
        $entity = $this->em->find($class, $id);
        self::assertNotNull($entity);
        return $entity;
    }
}
