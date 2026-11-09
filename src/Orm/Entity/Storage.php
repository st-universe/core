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
use InvalidArgumentException;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\ModuleQualityPacket;
use Stu\Orm\Attribute\TruncateOnGameReset;
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
#[TruncateOnGameReset]
class Storage
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

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[ManyToOne(targetEntity: Colony::class, inversedBy: 'storage')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Colony $colony = null;

    #[ManyToOne(targetEntity: Spacecraft::class, inversedBy: 'storage')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Spacecraft $spacecraft = null;

    #[OneToOne(targetEntity: TorpedoStorage::class, inversedBy: 'storage')]
    #[JoinColumn(name: 'torpedo_storage_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TorpedoStorage $torpedoStorage = null;

    #[ManyToOne(targetEntity: TradePost::class)]
    #[JoinColumn(name: 'tradepost_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradePost $tradePost = null;

    #[OneToOne(targetEntity: TradeOffer::class, inversedBy: 'storage')]
    #[JoinColumn(name: 'tradeoffer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradeOffer $tradeOffer = null;

    #[ManyToOne(targetEntity: Trumfield::class, inversedBy: 'storage')]
    #[JoinColumn(name: 'trumfield_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Trumfield $trumfield = null;

    /** @var array<int, int>|null */
    #[Column(name: 'module_quality', type: 'json', nullable: true)]
    private ?array $moduleQuality = null;

    /** @return array<int, int> */
    public function getModuleQualityAmounts(): array
    {
        $qualities = $this->moduleQuality ?? [];
        $standard = $this->count - array_sum($qualities);
        if ($standard > 0) {
            $qualities[0] = $standard;
        }
        ksort($qualities, SORT_NUMERIC);
        return $qualities;
    }

    /** @return list<array{steps: int, amount: int, label: string}> */
    public function getModuleQualityVariants(): array
    {
        $variants = [];
        foreach ($this->getModuleQualityAmounts() as $steps => $amount) {
            $variants[] = ['steps' => $steps, 'amount' => $amount, 'label' => $steps === 0 ? 'Standard' : '+' . number_format($steps / 10, 1, ',', '') . '%'];
        }
        return $variants;
    }

    public function hasImprovedModules(): bool
    {
        return ($this->moduleQuality ?? []) !== [];
    }

    public function getModuleQualityDescription(): string
    {
        return implode(', ', array_map(static fn (array $variant): string => $variant['amount'] . ' × ' . $variant['label'], $this->getModuleQualityVariants()));
    }

    /** @param array<int, int> $qualities */
    public function addModuleQualityAmounts(array $qualities): self
    {
        ModuleQualityPacket::validate($qualities);
        $updated = $this->moduleQuality ?? [];
        foreach ($qualities as $steps => $amount) {
            if ($steps !== 0 && $amount !== 0) {
                $updated[$steps] = ($updated[$steps] ?? 0) + $amount;
            }
        }
        if (array_sum($updated) > $this->count) {
            throw new InvalidArgumentException('Module qualities exceed storage amount');
        }
        $this->moduleQuality = $updated === [] ? null : $updated;
        return $this;
    }

    /** @return array<int, int> */
    public function removeAmount(int $amount, ?int $quality = null): array
    {
        $available = $this->getModuleQualityAmounts();
        if ($amount < 0 || $amount > $this->getAmountForQuality($quality)) {
            throw new InvalidArgumentException('Insufficient module quality stock');
        }
        $remaining = $amount;
        $removed = [];
        foreach ($available as $steps => $stored) {
            if ($remaining === 0) {
                break;
            }
            if ($quality !== null && $steps !== $quality) {
                continue;
            }
            $take = min($remaining, $stored);
            if ($take === 0) {
                continue;
            }
            $removed[$steps] = $take;
            $remaining -= $take;
            $this->lowerQualityAmount($steps, $stored - $take);
        }
        $this->count -= $amount;
        return $removed;
    }

    private function lowerQualityAmount(int $steps, int $amount): void
    {
        if ($steps === 0) {
            return;
        }
        if ($amount === 0) {
            unset($this->moduleQuality[$steps]);
        } else {
            $this->moduleQuality[$steps] = $amount;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(User $user): Storage
    {
        $this->user = $user;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function getAmountForQuality(?int $quality): int
    {
        return $quality === null ? $this->getAmount() : ($this->getModuleQualityAmounts()[$quality] ?? 0);
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): Storage
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Storage amount cannot be negative');
        }
        if ($amount < $this->count) {
            $this->removeAmount($this->count - $amount);
        } else {
            $this->count = $amount;
        }

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(Commodity $commodity): Storage
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getColony(): ?Colony
    {
        return $this->colony;
    }

    public function getSpacecraft(): ?Spacecraft
    {
        return $this->spacecraft;
    }

    public function setEntity(?EntityWithStorageInterface $entity): Storage
    {
        if ($entity === null) {
            $this->spacecraft = null;
            $this->colony = null;
        }
        if ($entity instanceof Spacecraft) {
            $this->spacecraft = $entity;
        }
        if ($entity instanceof Colony) {
            $this->colony = $entity;
        }

        return $this;
    }

    public function getTorpedoStorage(): ?TorpedoStorage
    {
        return $this->torpedoStorage;
    }

    public function setTorpedoStorage(TorpedoStorage $torpedoStorage): Storage
    {
        $this->torpedoStorage = $torpedoStorage;
        return $this;
    }

    public function getTradePost(): ?TradePost
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePost $tradePost): Storage
    {
        $this->tradePost = $tradePost;
        return $this;
    }

    public function getTradeOffer(): ?TradeOffer
    {
        return $this->tradeOffer;
    }

    public function setTradeOffer(TradeOffer $tradeOffer): Storage
    {
        $this->tradeOffer = $tradeOffer;
        return $this;
    }

    public function setTrumfield(Trumfield $trumfield): Storage
    {
        $this->trumfield = $trumfield;
        return $this;
    }
}
