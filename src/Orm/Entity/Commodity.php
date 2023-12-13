<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Commodity\CommodityTypeEnum;

#[Table(name: 'stu_commodity')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\CommodityRepository')]
class Commodity implements CommodityInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint')]
    private int $sort = 0;

    #[Column(type: 'boolean')]
    private bool $view = true;

    #[Column(type: 'smallint')]
    private int $type = CommodityTypeEnum::COMMODITY_TYPE_STANDARD;

    #[Column(type: 'boolean')]
    private bool $npc_commodity = false;

    #[Column(type: 'boolean')]
    private bool $bound = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CommodityInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): CommodityInterface
    {
        $this->sort = $sort;

        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): CommodityInterface
    {
        $this->view = $view;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $typeId): CommodityInterface
    {
        $this->type = $typeId;

        return $this;
    }

    public function isTradeable(): bool
    {
        return $this->isBeamable() && $this->npc_commodity === false;
    }

    public function isBeamable(UserInterface $user = null, UserInterface $targetUser = null): bool
    {
        $isBound = $user !== null && $targetUser !== null && $this->isBoundToAccount() && $user !== $targetUser;

        return $this->getType() === CommodityTypeEnum::COMMODITY_TYPE_STANDARD && $this->getView() === true && !$isBound;
    }

    public function isSaveable(): bool
    {
        return $this->getType() === CommodityTypeEnum::COMMODITY_TYPE_STANDARD;
    }

    public function isBoundToAccount(): bool
    {
        return $this->bound;
    }

    public function isShuttle(): bool
    {
        return in_array(intdiv($this->getId(), 10) * 10, CommodityTypeEnum::BASE_IDS_SHUTTLE);
    }

    public function isWorkbee(): bool
    {
        return intdiv($this->getId(), 10) * 10 === CommodityTypeEnum::BASE_ID_WORKBEE;
    }

    /**
     * @deprecated
     */
    public function isIllegal(int $network): bool
    {
        // @todo remove
        return false;
    }

    public function getTransferCount(): int
    {
        // @todo Anzahl Waren pro Energieeinheit
        // Möglicherweise einstellbar nach Warentyp
        return 1;
    }
}
