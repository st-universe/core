<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AcceptApplication implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACCEPT_APPLICATION';

    private $acceptApplicationRequest;

    private $allianceJobRepository;

    private $allianceActionManager;

    private $privateMessageSender;

    public function __construct(
        AcceptApplicationRequestInterface $acceptApplicationRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->acceptApplicationRequest = $acceptApplicationRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Applications::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $alliance = $game->getUser()->getAlliance();

        if (!$this->allianceActionManager->mayEdit((int) $alliance->getId(), $userId)) {
            throw new AccessViolation();
        }

        $appl = $this->allianceJobRepository->find($this->acceptApplicationRequest->getApplicationId());
        if ($appl === null || $appl->getAlliance()->getId() !== $alliance->getId()) {
            new AccessViolation;
        }

        $applicant = $appl->getUser();
        $applicant->setAllianceId($alliance->getId());
        $applicant->save();

        $this->allianceJobRepository->delete($appl);

        $text = sprintf(
            _('Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz %s'),
            $alliance->getName()
        );

        $this->privateMessageSender->send($userId, $applicant->getId(), $text);

        $game->addInformation(_('Die Bewerbung wurde angenommen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
