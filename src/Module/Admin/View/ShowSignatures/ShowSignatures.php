<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class ShowSignatures implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'ADMIN_SHOW_SIGNATURES';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->loggerUtil->init();

        $shipId = request::postInt('shipid');
        $userId = request::postInt('userid');
        $allyId = request::postInt('allyid');

        $game->setTemplateVar('DONOTHING', true);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setMacro('html/admin/adminmacros.xhtml/signaturescan');

        $result = $this->flightSignatureRepository->getSignatureRangeForUser($userId);

        $game->setTemplateVar('SIGNATURE_PANEL', new SignaturePanel(
            $userId,
            current($result),
            $this->loggerUtil
        ));

        $game->setTemplateVar('DONOTHING', false);
    }
}
