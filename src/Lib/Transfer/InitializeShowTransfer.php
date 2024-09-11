<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use RuntimeException;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

final class InitializeShowTransfer implements InitializeShowTransferInterface
{
    /** @param array<TransferStrategyInterface> $transferStrategies */
    public function __construct(
        private BeamUtilInterface $beamUtil,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private array $transferStrategies
    ) {}

    public function init(
        ColonyInterface|ShipInterface $from,
        ColonyInterface|ShipInterface $to,
        bool $isUnload,
        TransferTypeEnum $transferType,
        GameControllerInterface $game
    ): void {
        $user = $game->getUser();

        $transferInformation = new TransferInformation(
            $transferType,
            $from,
            $to,
            $isUnload,
            $this->playerRelationDeterminator->isFriend($to->getUser(), $from->getUser())
        );

        $this->setPageTitle($transferInformation, $game);

        $game->setTemplateVar('TARGET', $to);
        $game->setTemplateVar('OWNS_TARGET', $to->getUser() === $user);
        $game->setTemplateVar('TRANSFER_INFO', $transferInformation);

        $strategy = $this->getTransferStrategy($transferType);
        $strategy->setTemplateVariables($isUnload, $from, $to, $game);
    }


    private function setPageTitle(
        TransferInformation $transferInformation,
        GameControllerInterface $game
    ): void {
        $game->setPageTitle(sprintf(
            '%s %s %s %s',
            $transferInformation->getTransferType()->getGoodName(),
            $transferInformation->isUnload() ? 'zu' : 'von',
            $transferInformation->isColonyTarget() ? 'Kolonie' : 'Schiff',
            $this->beamUtil->isDockTransfer($transferInformation->getSource(), $transferInformation->getTarget()) ? 'transferieren' : 'beamen'
        ));
    }

    private function getTransferStrategy(TransferTypeEnum $transferType): TransferStrategyInterface
    {
        if (!array_key_exists($transferType->value, $this->transferStrategies)) {
            throw new RuntimeException(sprintf('transfer strategy with typeValue %d does not exist', $transferType->value));
        }

        return $this->transferStrategies[$transferType->value];
    }
}
