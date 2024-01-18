<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

enum TransferTypeEnum: int
{
    case COMMODITIES = 1;
    case CREW = 2;
    case TORPEDOS = 3;

    public function getGoodName(): string
    {
        return match ($this) {
            self::COMMODITIES => 'Waren',
            self::CREW => 'Crew',
            self::TORPEDOS => 'Torpedos',
        };
    }

    public function getGoodsTemplate(): string
    {
        return match ($this) {
            self::COMMODITIES => 'html/transfer/good/commodities.twig',
            self::CREW => 'html/transfer/good/crew.twig',
            self::TORPEDOS => 'html/transfer/good/torpedos.twig',
        };
    }

    public function getActionsTemplate(): string
    {
        return match ($this) {
            self::COMMODITIES => 'html/transfer/action/commodityActions.twig',
            self::CREW => 'html/transfer/action/crewActions.twig',
            self::TORPEDOS => 'html/transfer/action/torpedoActions.twig',
        };
    }
}
