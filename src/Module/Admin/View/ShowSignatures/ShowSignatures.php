<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class ShowSignatures implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'ADMIN_SHOW_SIGNATURES';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $shipId = request::postInt('shipid');
        $userId = request::postInt('userid');
        $allyId = request::postInt('allyid');

        $game->setTemplateVar('DONOTHING', true);

        $game->showMacro('html/admin/adminmacros.xhtml/signaturescan');

        if ($shipId) {
        } else if ($userId) {
            $result = $this->flightSignatureRepository->getSignatureRangeForUser($userId);
        } else if ($allyId) {
            $result = $this->flightSignatureRepository->getSignatureRangeForAlly($allyId);
        }

        $game->setTemplateVar('SIGNATURE_PANEL', new SignaturePanel(
            $userId,
            $allyId,
            current($result),
            $this->loggerUtilFactory->getLoggerUtil()
        ));

        $game->setTemplateVar('DONOTHING', false);
    }
}
