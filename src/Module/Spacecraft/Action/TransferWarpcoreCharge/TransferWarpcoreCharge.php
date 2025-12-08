<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferWarpcoreCharge;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SpacecraftManagement\Manager\ManageWarpcoreTransfer;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderSpacecraft;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowWarpcoreChargeTransfer\ShowWarpcoreChargeTransfer;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class TransferWarpcoreCharge implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER_WARPCORE_CHARGE';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private InteractionCheckerInterface $interactionChecker,
        private ManageWarpcoreTransfer $manageWarpcoreTransfer
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowWarpcoreChargeTransfer::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER)) {
            $game->getInfo()->addInformation('Dieses Schiff verfügt über kein Warpkern Ladungstransfer System');
            return;
        }

        if (
            $spacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE) ||
            $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SHIELDS)
        ) {
            $game->getInfo()->addInformation('Warpantrieb und Schilde müssen deaktiviert sein');
            return;
        }

        $sourceReactor = $wrapper->getReactorWrapper();

        if (!$sourceReactor || $sourceReactor->getLoad() <= 0) {
            $game->getInfo()->addInformation('Kein Warpkern oder keine Ladung vorhanden');
            return;
        }

        $shipIds = request::postArray('spacecrafts');
        if (count($shipIds) == 0) {
            $game->getInfo()->addInformation('Es wurden keine Schiffe ausgewählt');
            return;
        }

        $msg = [];

        $managerProvider = new ManagerProviderSpacecraft($wrapper);

        $values = [
            'warpcore_transfer' => request::postArray('warpcore_transfer'),
        ];

        foreach ($shipIds as $shipId) {
            $msg = array_merge($msg, $this->handleShip($values, $managerProvider, (int)$shipId, $wrapper));
        }

        $this->spacecraftRepository->save($spacecraft);

        $game->getInfo()->addInformationArray($msg, true);
    }

    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    private function handleShip(
        array $values,
        ManagerProviderSpacecraft $managerProvider,
        int $shipId,
        SpacecraftWrapperInterface $sourceWrapper
    ): array {
        $ship = $this->spacecraftRepository->find($shipId);
        if ($ship === null) {
            return [];
        }
        if ($ship->isCloaked()) {
            return [];
        }
        if (!$this->interactionChecker->checkPosition($sourceWrapper->get(), $ship)) {
            return [];
        }

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($ship);

        $msg = $this->manageWarpcoreTransfer->manage($wrapper, $values, $managerProvider);

        $this->spacecraftRepository->save($ship);

        return $msg;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
