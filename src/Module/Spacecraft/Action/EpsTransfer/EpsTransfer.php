<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\EpsTransfer;

use Override;
use request;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class EpsTransfer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ETRANSFER';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }

        $ship = $wrapper->get();
        $target = $targetWrapper->get();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game->getInfo())) {
            return;
        }

        $eps = $wrapper->getEpsSystemData();

        if ($eps === null || $eps->getEps() == 0) {
            $game->getInfo()->addInformation(_("Keine Energie vorhanden"));
            return;
        }

        $load = request::postInt('ecount');
        if ($load < 1) {
            $game->getInfo()->addInformation(_("Es wurde keine Energiemenge angegeben"));
            return;
        }

        $targetEps = $targetWrapper->getEpsSystemData();

        if ($targetEps === null) {
            $game->getInfo()->addInformation(sprintf(_('Die %s hat kein Energiesystem installiert'), $target->getName()));
            return;
        }
        if ($targetEps->getBattery() >= $targetEps->getMaxBattery()) {
            $game->getInfo()->addInformation(sprintf(_('Die Ersatzbatterie der %s ist bereits voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $eps->getEps()) {
            $load = (int) floor($eps->getEps() / 3);
        }
        if ($load + $targetEps->getBattery() > $targetEps->getMaxBattery()) {
            $load = $targetEps->getMaxBattery() - $targetEps->getBattery();
        }
        $eps->lowerEps($load * 3)->update();
        $targetEps->setBattery($targetEps->getBattery() + $load)->update();

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            "Die " . $ship->getName() . " transferiert in Sektor " . $ship->getSectorString() . " " . $load . " Energie in die Batterie der " . $target->getName(),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
        $game->getInfo()->addInformation(sprintf(_('Es wurde %d Energie zur %s transferiert'), $load, $target->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
