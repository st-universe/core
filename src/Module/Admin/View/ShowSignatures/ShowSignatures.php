<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowSignatures implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'ADMIN_SHOW_SIGNATURES';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $shipId = request::postInt('shipid');
        $userId = request::postInt('userid');
        $allyId = request::postInt('allyid');

        $game->setTemplateVar('DONOTHING', true);

        $game->showMacro('html/admin/adminmacros.xhtml/signaturescan');

        $signatureRange = [];

        if ($shipId !== 0) {
            $game->addInformation(_('Aktion noch nicht möglich für Einzelschiff'));
            return;
        } elseif ($userId !== 0) {
            $signatureRange = $this->flightSignatureRepository->getSignatureRangeForUser($userId);
        } elseif ($allyId !== 0) {
            $signatureRange = $this->flightSignatureRepository->getSignatureRangeForAlly($allyId);
        }

        if ($signatureRange === []) {
            return;
        }

        $game->setTemplateVar('SIGNATURE_PANEL', new SignaturePanel(
            $this->shipRepository,
            $userId,
            $allyId,
            $this->loggerUtilFactory->getLoggerUtil(),
            current($signatureRange)
        ));

        $game->setTemplateVar('DONOTHING', false);
    }
}
