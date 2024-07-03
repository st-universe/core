<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use JBBCode\Parser;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class EditDetails implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_UPDATE_ALLIANCE';

    private EditDetailsRequestInterface $editDetailsRequest;

    private Parser $bbcodeParser;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private AllianceRepositoryInterface $allianceRepository;

    public function __construct(
        EditDetailsRequestInterface $editDetailsRequest,
        Parser $bbcodeParser,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->editDetailsRequest = $editDetailsRequest;
        $this->bbcodeParser = $bbcodeParser;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = $alliance->getId();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            throw new AccessViolation();
        }

        $name = $this->editDetailsRequest->getName();
        if (!CleanTextUtils::checkBBCode($name)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $name = CleanTextUtils::clearEmojis($this->editDetailsRequest->getName());
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($name);
        if ($name !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
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

            $result = $this->allianceJobRepository->getByAllianceAndType(
                $allianceId,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            );

            foreach ($result as $applicant) {
                $text = sprintf(
                    _('Deine Bewerbung bei der Allianz %s wurde abgelehnt'),
                    $alliance->getName()
                );
                $this->privateMessageSender->send(UserEnum::USER_NOONE, $applicant->getUserId(), $text);
            }
        }

        if (mb_strlen(trim($this->bbcodeParser->parse($name)->getAsText())) < 5) {
            $game->addInformation(_('Der Name muss aus mindestens 5 Zeichen bestehen'));
            return;
        }

        if (mb_strlen($homepage) > 0 && strpos($homepage, 'http') !== 0) {
            $game->addInformation(_('Diese Homepage-Adresse ist nicht gültig'));
            return;
        }

        if (strlen($rgbCode) > 0) {
            if (strlen($rgbCode) != 7) {
                $game->addInformation(_('Der RGB-Code muss sieben Zeichen lang sein, z.B. #11ff67'));
                return;
            }

            if (!$this->validHex($rgbCode)) {
                $game->addInformation(_('Der RGB-Code ist ungültig!'));
                return;
            }

            $alliance->setRgbCode($rgbCode);
        }

        $alliance->setName($name);
        $alliance->setHomepage($homepage);
        $alliance->setDescription($description);

        $this->allianceRepository->save($alliance);

        $game->addInformation(_('Die Allianz wurde editiert'));
    }

    private function validHex(string $hex): int|bool
    {
        return preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
