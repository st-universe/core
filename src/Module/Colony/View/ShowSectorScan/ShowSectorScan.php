<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSectorScan;

use Override;
use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    /** @var array<int, int> */
    private array $fadedSignaturesUncloaked = [];
    /** @var array<int, int> */
    private array $fadedSignaturesCloaked = [];

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private StarSystemMapRepositoryInterface $mapRepository,
        private FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId,
            false
        );

        $game->setPageTitle("Sektor Scan");

        $mapField = $this->mapRepository->getByCoordinates(
            $colony->getSystem()->getId(),
            $colony->getSx(),
            $colony->getSy()
        );

        if ($mapField === null) {
            return;
        }

        $game->setMacroInAjaxWindow('html/colony/component/sectorscan.twig');

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', $this->fadedSignaturesUncloaked === [] ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', $this->fadedSignaturesCloaked === [] ? null : count($this->fadedSignaturesCloaked));
    }

    /**
     * @return array<SignatureWrapper>
     */
    private function getSignatures(StarSystemMap $field, int $ignoreId): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field->getId(), $ignoreId);

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
                    $filteredSigs[sprintf('%d_%s', $id, $name)] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }
}
