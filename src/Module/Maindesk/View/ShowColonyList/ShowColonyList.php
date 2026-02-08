<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonyList implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    public function __construct(private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $state = $user->getState();

        if ($state !== UserStateEnum::UNCOLONIZED) {
            throw new AccessViolationException(sprintf(
                _('User is not uncolonized, but tried to enter first-colony-list. Fool: %d, State: %d'),
                $user->getId(),
                $state->value
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
        $layerInfo = [];

        foreach ($planets as $planet) {
            $system = $planet->getSystem();
            $layer = $system->getLayer();
            $map = $system->getMap();
            $adminRegion = $map?->getAdministratedRegion();

            $layerId = $layer?->getId() ?? 0;
            $layerName = $layer?->getName() ?? 'Unbekannte Ebene';
            $layerDescription = $layer?->getDescription();
            $isNoobzone = $layer?->isNoobzone() ?? false;
            $regionId = $adminRegion?->getId() ?? 0;
            $regionName = $adminRegion?->getDescription() ?? 'Unbekannte Region';

            if (!isset($layerInfo[$layerId])) {
                $layerInfo[$layerId] = [
                    'is_noobzone' => $isNoobzone
                ];
            }

            if (!isset($grouped[$layerId])) {
                $grouped[$layerId] = [
                    'layer_name' => $layerName,
                    'layer_description' => $layerDescription,
                    'is_noobzone' => $isNoobzone,
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

        $sortedKeys = array_keys($grouped);
        usort($sortedKeys, function ($a, $b) use ($layerInfo) {
            $isANoobzone = $layerInfo[$a]['is_noobzone'] ?? false;
            $isBNoobzone = $layerInfo[$b]['is_noobzone'] ?? false;

            if ($isANoobzone !== $isBNoobzone) {
                return $isBNoobzone ? 1 : -1;
            }

            return $a <=> $b;
        });

        $sortedGrouped = [];
        foreach ($sortedKeys as $key) {
            $sortedGrouped[$key] = $grouped[$key];
        }

        return $sortedGrouped;
    }

    #[\Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::MAINDESK, self::VIEW_IDENTIFIER);
    }
}
