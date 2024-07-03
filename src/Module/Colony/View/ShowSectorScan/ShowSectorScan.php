<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSectorScan;

use Stu\Orm\Entity\StarSystemMapInterface;
use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    private ColonyLoaderInterface $colonyLoader;

    private StarSystemMapRepositoryInterface $mapRepository;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private array $fadedSignaturesUncloaked = [];
    private array $fadedSignaturesCloaked = [];

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        StarSystemMapRepositoryInterface $mapRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->mapRepository = $mapRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId,
            false
        );

        $game->setPageTitle("Sektor Scan");
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/sectorscan');

        $game->setTemplateVar('ERROR', true);

        $mapField = $this->mapRepository->getByCoordinates(
            $colony->getSystem()->getId(),
            $colony->getSx(),
            $colony->getSy()
        );

        if ($mapField === null) {
            return;
        }

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, true, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', $this->fadedSignaturesUncloaked === [] ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', $this->fadedSignaturesCloaked === [] ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('ERROR', false);
    }

    /**
     * @return array<string, SignatureWrapper>
     */
    private function getSignatures(StarSystemMapInterface $field, bool $isSystem, int $ignoreId): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field->getId(), $isSystem, $ignoreId);

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
}
