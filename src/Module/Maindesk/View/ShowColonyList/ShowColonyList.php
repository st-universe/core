<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonyList implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    public function __construct(private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $state = $user->getState();

        if ($state !== UserEnum::USER_STATE_UNCOLONIZED) {
            throw new AccessViolationException(sprintf(
                _('User is not uncolonized, but tried to enter first-colony-list. Fool: %d, State: %d'),
                $user->getId(),
                $state
            ));
        }
        $game->setViewTemplate("html/maindesk/colonylist.twig");
        $game->setPageTitle("Kolonie gründen");
        $game->appendNavigationPart(
            sprintf(
                '?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Kolonie gründen')
        );

        $freePlanets = $this->colonyRepository->getStartingByFaction($user->getFactionId());
        $groupedPlanets = $this->groupPlanetsByLayerAndRegion($freePlanets);

        $game->setTemplateVar('GROUPED_PLANETS', $groupedPlanets);
    }

    /**
     * @param array<Colony> $planets
     * @return array<int, array{layer_name: string, layer_description: string|null, regions: array<int, array{region_name: string, planets: array<Colony>}>}>
     */
    private function groupPlanetsByLayerAndRegion(array $planets): array
    {
        $grouped = [];

        foreach ($planets as $planet) {
            $system = $planet->getSystem();
            $layer = $system->getLayer();
            $map = $system->getMap();
            $adminRegion = $map?->getAdministratedRegion();

            $layerId = $layer?->getId() ?? 0;
            $layerName = $layer?->getName() ?? 'Unbekannte Ebene';
            $layerDescription = $layer?->getDescription();
            $regionId = $adminRegion?->getId() ?? 0;
            $regionName = $adminRegion?->getDescription() ?? 'Unbekannte Region';

            if (!isset($grouped[$layerId])) {
                $grouped[$layerId] = [
                    'layer_name' => $layerName,
                    'layer_description' => $layerDescription,
                    'regions' => []
                ];
            }

            if (!isset($grouped[$layerId]['regions'][$regionId])) {
                $grouped[$layerId]['regions'][$regionId] = [
                    'region_name' => $regionName,
                    'planets' => []
                ];
            }

            $grouped[$layerId]['regions'][$regionId]['planets'][] = $planet;
        }

        ksort($grouped);

        return $grouped;
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::MAINDESK, self::VIEW_IDENTIFIER);
    }
}
