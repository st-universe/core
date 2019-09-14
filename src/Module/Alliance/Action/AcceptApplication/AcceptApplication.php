<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use AccessViolation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AcceptApplication implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACCEPT_APPLICATION';

    private $acceptApplicationRequest;

    private $allianceJobRepository;

    public function __construct(
        AcceptApplicationRequestInterface $acceptApplicationRequest,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->acceptApplicationRequest = $acceptApplicationRequest;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Applications::VIEW_IDENTIFIER);

        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }

        $appl = $this->allianceJobRepository->find($this->acceptApplicationRequest->getApplicationId());
        if ($appl === null || $appl->getAllianceId() != $alliance->getId()) {
            new AccessViolation;
        }

        $user = $appl->getUser();
        $user->setAllianceId($appl->getAllianceId());
        $user->save();

        $this->allianceJobRepository->delete($appl);

        $text = sprintf(
            _('Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz %s'),
            $alliance->getNameWithoutMarkup()
        );
        PM::sendPM($game->getUser()->getId(), $user->getId(), $text);

        $game->addInformation(_('Die Bewerbung wurde angenommen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
