<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalSpacecrafts;

use request;
use Stu\Lib\SpacecraftManagement\HandleManagersInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ManageOrbitalSpacecrafts implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_SPACECRAFTS';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyRepositoryInterface $colonyRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ManagerProviderFactoryInterface $managerProviderFactory,
        private HandleManagersInterface $handleManagers
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrbitManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $spacecraftIds = request::postArray('spacecrafts');
        if (count($spacecraftIds) == 0) {
            $game->getInfo()->addInformation(_('Es wurden keine Schiffe ausgewählt'));
            return;
        }
        $msg = [];

        $managerProvider = $this->managerProviderFactory->getManagerProviderColony($colony);

        $values = [
            'batt' => request::postArray('batt'),
            'crew' => request::postArray('crew'),
            'reactor' => request::postArray('reactor'),
            'torp' => request::postArray('torp'),
            'torp_type' => request::postArray('torp_type'),
        ];

        foreach ($spacecraftIds as $spacecraftId) {
            $msg = array_merge($msg, $this->handleSpacecraft($values, $managerProvider, (int)$spacecraftId, $colony));
        }
        $this->colonyRepository->save($colony);

        $game->getInfo()->addInformationArray($msg, true);
    }

    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    private function handleSpacecraft(
        array $values,
        ManagerProviderInterface $managerProvider,
        int $spacecraftId,
        Colony $colony
    ): array {
        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null) {
            return [];
        }
        if ($spacecraft->isCloaked()) {
            return [];
        }
        if ($colony->getLocation() !== $spacecraft->getLocation()) {
            return [];
        }

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

        $msg = $this->handleManagers->handle($wrapper, $values, $managerProvider);

        $this->spacecraftRepository->save($spacecraft);

        return $msg;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
