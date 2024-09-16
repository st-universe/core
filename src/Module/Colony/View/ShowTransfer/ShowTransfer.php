<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTransfer;

use Override;
use request;
use RuntimeException;
use Stu\Lib\Transfer\InitializeShowTransferInterface;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShowTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRANSFER';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private TransferTargetLoaderInterface $transferTargetLoader,
        private InteractionCheckerInterface $interactionChecker,
        private InitializeShowTransferInterface $initializeShowTransfer
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colonyId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');
        $isUnload = request::getIntFatal('is_unload') === 1;
        $transferType = TransferTypeEnum::from(request::getIntFatal('transfer_type'));

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $colonyId,
            $user->getId(),
            false
        );

        $target = $this->transferTargetLoader->loadTarget($targetId, false, false);
        if (!$target instanceof ShipInterface) {
            throw new RuntimeException('this should not happen');
        }

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        if (!$this->interactionChecker->checkColonyPosition($colony, $target) || ($target->getCloakState() && $target->getUser() !== $user)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/transfer/colony/colonyTransfer.twig');

        $game->setTemplateVar('COLONY', $colony);
        $this->initializeShowTransfer->init(
            $colony,
            $target,
            $isUnload,
            $transferType,
            $game
        );
    }
}
