<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSectorScan;

use Override;
use request;
use Stu\Component\Database\AchievementManagerInterface;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    /** @var array<int> */
    private array $fadedSignaturesUncloaked = [];

    /** @var array<int> */
    private array $fadedSignaturesCloaked = [];

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private EncodedMapInterface $encodedMap,
        private readonly AchievementManagerInterface $achievementManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );

        $game->setPageTitle("Sektor Scan");
        $game->setMacroInAjaxWindow('');
        $ship = $wrapper->get();

        if (!$ship->getNbs()) {
            $game->addInformation("Die Nahbereichssensoren sind nicht aktiv");
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

        $game->setMacroInAjaxWindow('html/spacecraft/sectorScan.twig');

        $epsSystem->lowerEps(1)->update();

        $mapField = $ship->getLocation();

        $colonyClass = $mapField->getFieldType()->getColonyClass();
        $this->achievementManager->checkDatabaseItem($colonyClass?->getDatabaseId(), $user);
        $this->checkDatabaseItemForMap($mapField, $user);

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField->getId(), $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', $this->fadedSignaturesUncloaked === [] ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', $this->fadedSignaturesCloaked === [] ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('MAP_PATH', $this->getMapPath($ship));
        $game->setTemplateVar('BUOYS', $ship->getLocation()->getBuoys());
    }

    private function checkDatabaseItemForMap(Location $location, User $user): void
    {
        if (!$location instanceof Map) {
            return;
        }

        $system = $location->getSystem();
        if (
            $system !== null
            && $location->getFieldType()->getIsSystem()
        ) {
            $this->achievementManager->checkDatabaseItem($system->getSystemType()->getDatabaseEntryId(), $user);
        }
    }

    /**
     * @return array<string, SignatureWrapper>
     */
    private function getSignatures(int $fieldId, int $ignoreId): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($fieldId, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShipId();
            $name = $sig->getShipName();

            if (!array_key_exists($id . '_' . $name, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() === null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
                            $this->fadedSignaturesCloaked[$id] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[$id] = $id;
                    }
                } else {
                    $filteredSigs[$id . '_' . $name] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }

    private function getMapPath(Spacecraft $spacecraft): string
    {
        $currentMapField = $spacecraft->getLocation();
        $layer = $currentMapField->getLayer();

        if ($currentMapField instanceof Map && $layer !== null) {
            return $this->encodedMap->getEncodedMapPath($currentMapField->getFieldId(), $layer);
        } else {
            return sprintf('%d.png', $currentMapField->getFieldId());
        }
    }
}
