<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferWarpcoreCharge;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SpacecraftManagement\Manager\ManageWarpcoreTransfer;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderSpacecraft;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
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
        private ManageWarpcoreTransfer $manageWarpcoreTransfer,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

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
        $transfersByUser = [];

        $managerProvider = new ManagerProviderSpacecraft($wrapper);

        $values = [
            'warpcore_transfer' => request::postArray('warpcore_transfer'),
        ];

        foreach ($shipIds as $shipId) {
            $targetShip = $this->spacecraftRepository->find((int)$shipId);
            if ($targetShip === null) {
                continue;
            }
            if ($targetShip->isCloaked()) {
                continue;
            }
            if (!$this->interactionChecker->checkPosition($spacecraft, $targetShip)) {
                continue;
            }

            $targetWrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($targetShip);

            $messages = $this->manageWarpcoreTransfer->manage($targetWrapper, $values, $managerProvider);

            if (!empty($messages) && $spacecraft->getUser() !== $targetShip->getUser()) {
                $targetUserId = $targetShip->getUser()->getId();
                if (!isset($transfersByUser[$targetUserId])) {
                    $transfersByUser[$targetUserId] = [
                        'transfers' => [],
                        'sectorString' => $targetShip->getSectorString()
                    ];
                }

                $transfersByUser[$targetUserId]['transfers'][] = [
                    'shipName' => $targetShip->getName()
                ];
            }

            $msg = array_merge($msg, $messages);

            $this->spacecraftRepository->save($targetShip);
        }

        $this->spacecraftRepository->save($spacecraft);

        $this->sendPrivateMessages($transfersByUser, $managerProvider, $spacecraft);

        $game->getInfo()->addInformationArray($msg, true);
    }

    /**
     * @param array<int, array<string, mixed>> $transfersByUser
     */
    private function sendPrivateMessages(
        array $transfersByUser,
        ManagerProviderSpacecraft $managerProvider,
        \Stu\Orm\Entity\Spacecraft $sourceSpacecraft
    ): void {
        $sourceUser = $sourceSpacecraft->getUser();

        foreach ($transfersByUser as $targetUserId => $data) {
            if ($targetUserId === $sourceUser->getId()) {
                continue;
            }

            $transfers = $data['transfers'];
            $sectorString = $data['sectorString'];

            if (empty($transfers)) {
                continue;
            }

            if (count($transfers) === 1) {
                $transfer = $transfers[0];
                $message = sprintf(
                    'Die %s hat in Sektor %s den Warpkern der %s aufgeladen',
                    $managerProvider->getName(),
                    $sectorString,
                    $transfer['shipName']
                );
            } else {
                $shipList = [];
                foreach ($transfers as $transfer) {
                    $shipList[] = $transfer['shipName'];
                }

                $message = sprintf(
                    'Die %s hat in Sektor %s die Warpkerne folgender Schiffe aufgeladen:\n%s',
                    $managerProvider->getName(),
                    $sectorString,
                    implode(', ', $shipList)
                );
            }

            $this->privateMessageSender->send(
                $sourceUser->getId(),
                $targetUserId,
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
