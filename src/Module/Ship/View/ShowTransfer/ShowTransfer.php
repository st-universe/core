<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTransfer;

use request;
use RuntimeException;
use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private TransferTargetLoaderInterface $transferTargetLoader;

    private BeamUtilInterface $beamUtil;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    /** @var array<TransferStrategyInterface> */
    private array $transferStrategies;

    /** @param array<TransferStrategyInterface> $transferStrategies */
    public function __construct(
        ShipLoaderInterface $shipLoader,
        TransferTargetLoaderInterface $transferTargetLoader,
        BeamUtilInterface $beamUtil,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        array $transferStrategies
    ) {
        $this->shipLoader = $shipLoader;
        $this->transferTargetLoader = $transferTargetLoader;
        $this->beamUtil = $beamUtil;
        $this->playerRelationDeterminator = $playerRelationDeterminator;
        $this->transferStrategies = $transferStrategies;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');
        $isUnload = request::getIntFatal('is_unload') === 1;
        $isColonyTarget = request::getIntFatal('is_colony') === 1;
        $transferType = TransferTypeEnum::from(request::getIntFatal('transfer_type'));

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $user->getId(),
            true,
            false
        );

        $ship = $wrapper->get();

        $target = $this->transferTargetLoader->loadTarget($targetId, $isColonyTarget, false);

        $transferInformation = new TransferInformation(
            $transferType,
            $ship,
            $target,
            $isUnload,
            $this->playerRelationDeterminator->isFriend($target->getUser(), $ship->getUser())
        );

        $this->setPageTitle($transferInformation, $game);
        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/transfer/ship/shipTransfer.twig');

        $game->setTemplateVar('TARGET', $target);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('OWNS_TARGET', $target->getUser() === $user);
        $game->setTemplateVar('TRANSFER_INFO', $transferInformation);

        $strategy = $this->getTransferStrategy($transferType);
        $strategy->setTemplateVariables($isUnload, $ship, $target, $game);
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
