<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use JBBCode\Parser;
use Stu\Exception\AccessViolationException;
use Stu\Lib\CleanTextUtils;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\AllianceSettingsRepositoryInterface;

final class EditDetails implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPDATE_ALLIANCE';

    public function __construct(
        private EditDetailsRequestInterface $editDetailsRequest,
        private Parser $bbcodeParser,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private PrivateMessageSenderInterface $privateMessageSender,
        private AllianceRepositoryInterface $allianceRepository,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new AccessViolationException();
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)) {
            throw new AccessViolationException();
        }

        $name = $this->editDetailsRequest->getName();
        if (!CleanTextUtils::checkBBCode($name)) {
            $game->getInfo()->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $name = CleanTextUtils::clearEmojis($this->editDetailsRequest->getName());
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($name);
        if ($name !== $nameWithoutUnicode) {
            $game->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($name) > 255) {
            $game->getInfo()->addInformation(
                _('Der Name darf inklusive BBCode nur maximal 255 Zeichen lang sein')
            );
            return;
        }

        $faction_mode = $this->editDetailsRequest->getFactionMode();
        $description = $this->editDetailsRequest->getDescription();
        $homepage = $this->editDetailsRequest->getHomepage();
        $acceptApplications = $this->editDetailsRequest->getAcceptApplications();
        $rgbCode = $this->editDetailsRequest->getRgbCode();

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($this->allianceActionManager->mayEditFactionMode($alliance, $user->getFactionId())) {
            if ($faction_mode === 1) {
                $alliance->setFaction($user->getFaction());
            } else {
                $alliance->setFaction(null);
            }
        }

        if ($acceptApplications === 1) {
            $alliance->setAcceptApplications(true);
        } else {
            $alliance->setAcceptApplications(false);

            $applications = $this->allianceApplicationRepository->getByAlliance($allianceId);

            foreach ($applications as $application) {
                $text = sprintf(
                    _('Deine Bewerbung bei der Allianz %s wurde abgelehnt'),
                    $alliance->getName()
                );
                $this->privateMessageSender->send(UserConstants::USER_NOONE, $application->getUser()->getId(), $text);
                $this->allianceApplicationRepository->delete($application);
            }
        }

        if (mb_strlen(trim($this->bbcodeParser->parse($name)->getAsText())) < 5) {
            $game->getInfo()->addInformation(_('Der Name muss aus mindestens 5 Zeichen bestehen'));
            return;
        }

        if (mb_strlen($homepage) > 0 && strpos($homepage, 'http') !== 0) {
            $game->getInfo()->addInformation(_('Diese Homepage-Adresse ist nicht gültig'));
            return;
        }

        if (strlen($rgbCode) > 0) {
            if (strlen($rgbCode) != 7) {
                $game->getInfo()->addInformation(_('Der RGB-Code muss sieben Zeichen lang sein, z.B. #11ff67'));
                return;
            }

            if (!$this->validHex($rgbCode)) {
                $game->getInfo()->addInformation(_('Der RGB-Code ist ungültig!'));
                return;
            }

            $alliance->setRgbCode($rgbCode);
        }

        $this->updateJobTitle($this->editDetailsRequest->getJobIdFounder(), $this->editDetailsRequest->getJobTitleFounder(), $alliance, $game);
        $this->updateJobTitle($this->editDetailsRequest->getJobIdSuccessor(), $this->editDetailsRequest->getJobTitleSuccessor(), $alliance, $game);
        $this->updateJobTitle($this->editDetailsRequest->getJobIdDiplomatic(), $this->editDetailsRequest->getJobTitleDiplomatic(), $alliance, $game);

        $alliance->setName($name);
        $alliance->setHomepage($homepage);
        $alliance->setDescription($description);

        $this->allianceRepository->save($alliance);

        $game->getInfo()->addInformation(_('Die Allianz wurde editiert'));
    }

    private function updateJobTitle(int $jobId, string $title, \Stu\Orm\Entity\Alliance $alliance, GameControllerInterface $game): void
    {
        if ($jobId === 0 || $title === '') {
            return;
        }

        if (strlen($title) < 3) {
            $game->getInfo()->addInformation(_('Alle Postenbeschreibungen müssen mindestens 3 Zeichen lang sein'));
            return;
        }

        $job = $this->allianceJobRepository->find($jobId);
        if ($job !== null && $job->getAlliance()->getId() === $alliance->getId()) {
            $job->setTitle($title);
            $this->allianceJobRepository->save($job);
        }
    }

    private function validHex(string $hex): int|bool
    {
        return preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
