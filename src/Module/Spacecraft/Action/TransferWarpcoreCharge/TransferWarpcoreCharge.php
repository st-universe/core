<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferWarpcoreCharge;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\View\ShowWarpcoreChargeTransfer\ShowWarpcoreChargeTransfer;
use Stu\Orm\Entity\Spacecraft;
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
        private TransferWarpcoreChargeRequestInterface $transferWarpcoreChargeRequest
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowWarpcoreChargeTransfer::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            $this->transferWarpcoreChargeRequest->getSpacecraftId(),
            $userId
        );

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

        $sourceWrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
        $sourceReactor = $sourceWrapper->getReactorWrapper();

        if (!$sourceReactor || $sourceReactor->getLoad() <= 0) {
            $game->getInfo()->addInformation('Kein Warpkern oder keine Ladung vorhanden');
            return;
        }

        $spacecraftIds = $this->transferWarpcoreChargeRequest->getTargetSpacecraftIds();
        $transferAmounts = $this->transferWarpcoreChargeRequest->getTransferAmounts();

        if (count($spacecraftIds) == 0) {
            $game->getInfo()->addInformation('Es wurden keine Schiffe ausgewählt');
            return;
        }

        $msg = [];
        $totalTransferred = 0;

        foreach ($spacecraftIds as $spacecraftId) {
            $transferAmount = (int)($transferAmounts[$spacecraftId] ?? 0);

            if ($transferAmount <= 0) {
                continue;
            }

            $result = $this->transferToSpacecraft(
                $spacecraft,
                $sourceReactor,
                (int)$spacecraftId,
                $transferAmount,
                $totalTransferred
            );

            if ($result['success']) {
                $msg[] = $result['message'];
                $totalTransferred += $result['transferred'];
            } else {
                $msg[] = $result['message'];
            }
        }

        if ($totalTransferred > 0) {
            $sourceReactor->changeLoad(-$totalTransferred);
            $this->spacecraftRepository->save($spacecraft);
        }

        if (empty($msg)) {
            $game->getInfo()->addInformation('Keine gültigen Transfers durchgeführt');
        } else {
            $game->getInfo()->addInformationArray($msg, true);
        }
    }

    /**
     * @return array{success: bool, message: string, transferred: int}
     */
    private function transferToSpacecraft(
        Spacecraft $sourceSpacecraft,
        ReactorWrapperInterface $sourceReactor,
        int $targetSpacecraftId,
        int $requestedAmount,
        int $alreadyTransferred
    ): array {
        $targetSpacecraft = $this->spacecraftRepository->find($targetSpacecraftId);

        if ($targetSpacecraft === null) {
            return [
                'success' => false,
                'message' => 'Zielschiff nicht gefunden',
                'transferred' => 0
            ];
        }

        if ($targetSpacecraft->isCloaked()) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s ist getarnt', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        if (!$this->interactionChecker->checkPosition($sourceSpacecraft, $targetSpacecraft)) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s ist nicht in Reichweite', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        if (
            $targetSpacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE) ||
            $targetSpacecraft->getSystemState(SpacecraftSystemTypeEnum::SHIELDS)
        ) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s: Warpantrieb und Schilde müssen deaktiviert sein', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        if (!$targetSpacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE)) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s hat keinen Warpkern', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        $targetWrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($targetSpacecraft);
        $targetReactor = $targetWrapper->getReactorWrapper();

        if (!$targetReactor) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s: Warpkern nicht verfügbar', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        $availableSourceLoad = $sourceReactor->getLoad() - $alreadyTransferred;
        $targetCapacity = $targetReactor->getCapacity() - $targetReactor->getLoad();

        if ($availableSourceLoad <= 0) {
            return [
                'success' => false,
                'message' => 'Keine Warpkern-Ladung mehr verfügbar',
                'transferred' => 0
            ];
        }

        if ($targetCapacity <= 0) {
            return [
                'success' => false,
                'message' => sprintf('Schiff %s: Warpkern ist bereits voll geladen', $targetSpacecraft->getName()),
                'transferred' => 0
            ];
        }

        $actualTransfer = min($requestedAmount, $availableSourceLoad, $targetCapacity);

        $targetReactor->changeLoad((int)$actualTransfer);
        $this->spacecraftRepository->save($targetSpacecraft);

        return [
            'success' => true,
            'message' => sprintf(
                'Warpkern-Ladung von %d an Schiff %s übertragen',
                $actualTransfer,
                $targetSpacecraft->getName()
            ),
            'transferred' => $actualTransfer
        ];
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
