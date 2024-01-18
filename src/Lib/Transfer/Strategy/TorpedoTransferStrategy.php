<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use request;
use RuntimeException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

class TorpedoTransferStrategy implements TransferStrategyInterface
{
    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $logger;

    public function __construct(
        ShipTorpedoManagerInterface $shipTorpedoManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->privateMessageSender = $privateMessageSender;

        $this->logger = $loggerUtilFactory->getLoggerUtil();
        //$this->logger->init('TORP', LoggerEnum::LEVEL_ERROR);
    }

    public function setTemplateVariables(
        bool $isUnload,
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        if ($target instanceof ColonyInterface) {
            throw new RuntimeException('this should not happen');
        }

        if ($isUnload) {
            $max = min(
                $target->getMaxTorpedos(),
                $ship->getTorpedoCount()
            );
            $commodityId = $ship->getTorpedo() === null ? null : $ship->getTorpedo()->getCommodityId();
        } else {
            $max = $target->getTorpedoCount();
            $commodityId = $target->getTorpedo() === null ? null : $target->getTorpedo()->getCommodityId();
        }

        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('COMMODITY_ID', $commodityId);
    }

    public function transfer(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        if ($target instanceof ColonyInterface) {
            throw new RuntimeException('this should not happen');
        }

        $this->logger->log('A');

        $ship = $wrapper->get();

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            $game->addInformation(_("Das Torpedolager ist zerstört"));
            $this->logger->log('B');
            return;
        }
        $this->logger->log('C');

        if ($ship->getTorpedoCount() > 0 && $target->getTorpedoCount() > 0 && $ship->getTorpedo() !== $target->getTorpedo()) {
            $game->addInformation(_("Die Schiffe haben unterschiedliche Torpedos geladen"));
            $this->logger->log('D');
            return;
        }

        $this->logger->log('E');
        //TODO use energy to transfer

        $requestedTransferCount = request::postInt('tcount');

        $targetWrapper = $this->shipWrapperFactory->wrapShip($target);

        if ($isUnload) {
            $amount = min(
                $requestedTransferCount,
                $ship->getTorpedoCount(),
                $target->getMaxTorpedos() - $target->getTorpedoCount()
            );

            if ($amount > 0) {
                $torpedo = $ship->getTorpedo();
                if ($torpedo === null) {
                    throw new RuntimeException('torpedo should not be null');
                }

                if (
                    !$target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)
                    && $target->getRump()->getTorpedoLevel() !== $torpedo->getLevel()
                ) {
                    $game->addInformation(sprintf(_('Die %s kann den Torpedotyp nicht ausrüsten'), $target->getName()));
                    return;
                }

                $this->shipTorpedoManager->changeTorpedo($targetWrapper, $amount, $ship->getTorpedo());
                $this->shipTorpedoManager->changeTorpedo($wrapper, -$amount);
            }
        } else {
            $amount = min(
                $requestedTransferCount,
                $target->getTorpedoCount(),
                $ship->getMaxTorpedos() - $ship->getTorpedoCount()
            );

            if ($amount > 0) {
                $this->shipTorpedoManager->changeTorpedo($wrapper, $amount, $target->getTorpedo());
                $this->shipTorpedoManager->changeTorpedo($targetWrapper, -$amount);
            }
        }

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Torpedos %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        $this->privateMessageSender->send(
            $game->getUser()->getId(),
            $target->getUser()->getId(),
            sprintf(
                'Die %s hat in Sektor %s %d Torpedos %s %s transferiert',
                $ship->getName(),
                $ship->getSectorString(),
                $amount,
                $isUnload ? 'zur' : 'von der',
                $target->getName()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }
}
