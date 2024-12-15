<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\StorageRepository;

#[Table(name: 'stu_storage')]
#[Index(name: 'storage_user_idx', columns: ['user_id'])]
#[Index(name: 'storage_commodity_idx', columns: ['commodity_id'])]
#[Index(name: 'storage_colony_idx', columns: ['colony_id'])]
#[Index(name: 'storage_spacecraft_idx', columns: ['spacecraft_id'])]
#[Index(name: 'storage_torpedo_idx', columns: ['torpedo_storage_id'])]
#[Index(name: 'storage_tradepost_idx', columns: ['tradepost_id'])]
#[Index(name: 'storage_tradeoffer_idx', columns: ['tradeoffer_id'])]
#[Entity(repositoryClass: StorageRepository::class)]
class Storage implements StorageInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    //TODO rename to amount
    #[Column(type: 'integer')]
    private int $count = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $colony_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $spacecraft_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $torpedo_storage_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tradepost_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tradeoffer_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $trumfield_id = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[ManyToOne(targetEntity: 'Colony', inversedBy: 'storage')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ColonyInterface $colony = null;

    #[ManyToOne(targetEntity: 'Spacecraft', inversedBy: 'storage')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftInterface $spacecraft = null;

    #[OneToOne(targetEntity: 'TorpedoStorage', inversedBy: 'storage')]
    #[JoinColumn(name: 'torpedo_storage_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TorpedoStorageInterface $torpedoStorage = null;

    #[ManyToOne(targetEntity: 'TradePost')]
    #[JoinColumn(name: 'tradepost_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradePostInterface $tradePost = null;

    #[OneToOne(targetEntity: 'TradeOffer', inversedBy: 'storage')]
    #[JoinColumn(name: 'tradeoffer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradeOfferInterface $tradeOffer = null;

    #[ManyToOne(targetEntity: 'Trumfield', inversedBy: 'storage')]
    #[JoinColumn(name: 'trumfield_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TrumfieldInterface $trumfield = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setUser(UserInterface $user): StorageInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count;
    }

    #[Override]
    public function setAmount(int $amount): StorageInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(CommodityInterface $commodity): StorageInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    #[Override]
    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): StorageInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function getSpacecraft(): ?SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(?SpacecraftInterface $spacecraft): StorageInterface
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function getTorpedoStorage(): ?TorpedoStorageInterface
    {
        return $this->torpedoStorage;
    }

    #[Override]
    public function setTorpedoStorage(TorpedoStorageInterface $torpedoStorage): StorageInterface
    {
        $this->torpedoStorage = $torpedoStorage;
        return $this;
    }

    #[Override]
    public function getTradePost(): ?TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function setTradePost(TradePostInterface $tradePost): StorageInterface
    {
        $this->tradePost = $tradePost;
        return $this;
    }

    #[Override]
    public function getTradeOffer(): ?TradeOfferInterface
    {
        return $this->tradeOffer;
    }

    #[Override]
    public function setTradeOffer(TradeOfferInterface $tradeOffer): StorageInterface
    {
        $this->tradeOffer = $tradeOffer;
        return $this;
    }

    #[Override]
    public function setTrumfield(TrumfieldInterface $trumfield): StorageInterface
    {
        $this->trumfield = $trumfield;
        return $this;
    }
}
