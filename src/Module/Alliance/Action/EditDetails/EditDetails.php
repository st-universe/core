<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use AccessViolation;
use JBBCode\Parser;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Communication\Lib\PrivateMessageSender;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class EditDetails implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UPDATE_ALLIANCE';

    private $editDetailsRequest;

    private $bbcodeParser;

    private $allianceJobRepository;

    private $allianceActionManager;

    public function __construct(
        EditDetailsRequestInterface $editDetailsRequest,
        Parser $bbcodeParser,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->editDetailsRequest = $editDetailsRequest;
        $this->bbcodeParser = $bbcodeParser;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = (int)$alliance->getId();

        $name = $this->editDetailsRequest->getName();
        $faction_mode = $this->editDetailsRequest->getFactionMode();
        $description = $this->editDetailsRequest->getDescription();
        $homepage = $this->editDetailsRequest->getHomepage();
        $acceptApplications = $this->editDetailsRequest->getAcceptApplications();

        if (!$this->allianceActionManager->mayEdit($allianceId, $game->getUser()->getId())) {
            throw new AccessViolation();
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($this->allianceActionManager->mayEditFactionMode($alliance, (int) $user->getFaction())) {
            if ($faction_mode === 1) {
                $alliance->setFactionId($user->getFaction());
            } else {
                $alliance->setFactionId(0);
            }
        }
        if ($acceptApplications === 1) {
            $alliance->setAcceptApplications(true);
        } else {
            $alliance->setAcceptApplications(false);

            $result = $this->allianceJobRepository->getByAllianceAndType(
                $allianceId,
                ALLIANCE_JOBS_PENDING
            );

            foreach ($result as $applicant) {
                $text = sprintf(
                    _('Deine Bewerbung bei der Allianz %s wurde abgelehnt'),
                    $alliance->getName()
                );
                PrivateMessageSender::sendPM(USER_NOONE, $applicant->getUserId(), $text);

                $applicant->deleteFromDatabase();
            }
        }

        if (mb_strlen(trim($this->bbcodeParser->parse($name)->getAsText())) < 5) {
            $game->addInformation(_('Der Name muss aus mindestens 5 Zeichen bestehen'));
            return;
        }
        if (mb_strlen($homepage) > 0) {
            if (strpos($homepage, 'http') !== 0) {
                $game->addInformation(_('Diese Homepage-Adresse ist nicht gültig'));
                return;
            }
        }
        $alliance->setName($name);
        $alliance->setHomepage($homepage);
        $alliance->setDescription($description);
        $alliance->save();

        $game->addInformation(_('Die Allianz wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
