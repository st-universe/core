<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSectorScan;

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

    private $fadedSignaturesUncloaked = [];
    private $fadedSignaturesCloaked = [];

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

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle("Sektor Scan");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/sectorscan');

        $game->setTemplateVar('ERROR', true);

        $mapField = $this->mapRepository->getByCoordinates(
            $colony->getSystem()->getId(),
            $colony->getSx(),
            $colony->getSy()
        );

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, true, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('ERROR', false);
    }

    private function getSignatures($field, $isSystem, $ignoreId)
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field, $isSystem, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShip()->getId();

            if (!array_key_exists($id, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() == null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
                            $this->fadedSignaturesCloaked[$id] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[$id] = $id;
                    }
                } else {
                    $filteredSigs[$id] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }
}
