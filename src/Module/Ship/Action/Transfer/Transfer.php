<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Transfer;

use request;
use RuntimeException;
use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class Transfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private TransferTargetLoaderInterface $transferTargetLoader;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $logger;

    /** @var array<TransferStrategyInterface> */
    private array $transferStrategies;

    /** @param array<TransferStrategyInterface> $transferStrategies */

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TransferTargetLoaderInterface $transferTargetLoader,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        array $transferStrategies
    ) {
        $this->shipLoader = $shipLoader;
        $this->transferTargetLoader = $transferTargetLoader;
        $this->playerRelationDeterminator = $playerRelationDeterminator;
        $this->privateMessageSender = $privateMessageSender;
        $this->transferStrategies = $transferStrategies;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
        //$this->logger->init('TRANSFER', LoggerEnum::LEVEL_ERROR);
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::postIntFatal('id');
        $targetId = request::postIntFatal('target');
        $isUnload = request::postIntFatal('is_unload') === 1;
        $isColonyTarget = request::postIntFatal('is_colony') === 1;
        $transferType = TransferTypeEnum::from(request::postIntFatal('transfer_type'));

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $this->logger->log('T1');

        //bad request
        if (!$ship->hasEnoughCrew($game)) {
            $this->logger->log('T2');
            return;
        }

        $this->logger->log('T3');
        $target = $this->transferTargetLoader->loadTarget($targetId, $isColonyTarget);

        $transferInformation = new TransferInformation(
            $transferType,
            $ship,
            $target,
            $isUnload,
            $this->playerRelationDeterminator->isFriend($target->getUser(), $ship->getUser())
        );

        $this->logger->log('TS1');
        $this->sanityCheck($transferInformation);
        $this->logger->log('TS2');

        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            $this->logger->log('T4');
            return;
        }

        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            $this->logger->log('T5');
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            $this->logger->log('T6');
            return;
        }

        if ($ship->getWarpDriveState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            $this->logger->log('T7');
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            $this->logger->log('T8');
            return;
        }

        if ($target instanceof ShipInterface && $target->getWarpDriveState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            $this->logger->log('T9');
            return;
        }
        $this->logger->log('T10');

        $strategy = $this->getTransferStrategy($transferType);

        $informations = new InformationWrapper();

        $strategy->transfer($isUnload, $wrapper, $target, $informations);

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $target->getUser()->getId(),
            $informations->getInformationsAsString(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(
                '%s.php?%s=1&id=%d',
                $target instanceof ShipInterface ? 'ship' : 'colony',
                $target instanceof ShipInterface ? ShowShip::VIEW_IDENTIFIER : ShowColony::VIEW_IDENTIFIER,
                $target->getId()
            )
        );

        $game->addInformationWrapper($informations);
    }

    private function sanityCheck(TransferInformation $transferInformation): void
    {
        switch ($transferInformation->getTransferType()) {
            case TransferTypeEnum::COMMODITIES:
                if ($transferInformation->isCommodityTransferPossible(false)) {
                    return;
                }
                break;
            case TransferTypeEnum::CREW:
                if ($transferInformation->isCrewTransferPossible(false)) {
                    return;
                }
                break;
            case TransferTypeEnum::TORPEDOS:
                if ($transferInformation->isTorpedoTransferPossible(false)) {
                    return;
                }
                break;
        }

        throw new SanityCheckException(sprintf(
            'userId %d tried to transfer %s %s targetId %d (%s), but it is not possible',
            $transferInformation->getSource()->getUser()->getId(),
            $transferInformation->getTransferType()->getGoodName(),
            $transferInformation->isUnload() ? 'to' : 'from',
            $transferInformation->getTarget()->getId(),
            $transferInformation->isColonyTarget() ? 'colony' : 'ship'
        ));
    }

    private function getTransferStrategy(TransferTypeEnum $transferType): TransferStrategyInterface
    {
        if (!array_key_exists($transferType->value, $this->transferStrategies)) {
            throw new RuntimeException(sprintf('transfer strategy with typeValue %d does not exist', $transferType->value));
        }

        return $this->transferStrategies[$transferType->value];
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
