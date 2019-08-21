<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use AccessViolation;
use Alliance;
use AllianceJobsData;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class Signup implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    private $signupRequest;

    public function __construct(
        SignupRequestInterface $signupRequest
    ) {
        $this->signupRequest = $signupRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = new Alliance($this->signupRequest->getAllianceId());

        if (!$alliance->currentUserMaySignup()) {
            throw new AccessViolation();
        }
        $obj = new AllianceJobsData();
        $obj->setUserId($userId);
        $obj->setType(ALLIANCE_JOBS_PENDING);
        $obj->setAllianceId($alliance->getId());
        $obj->save();

        $text = sprintf(
            'Der Siedler %s hat sich für die Allianz beworben',
            $user->getNameWithoutMarkup()
        );
        PM::sendPM($userId, $alliance->getFounder()->getUserId(), $text);
        if ($alliance->getSuccessor()) {
            PM::sendPM($userId, $alliance->getSuccessor()->getUserId(), $text);
        }

        $game->addInformation(_('Deine Bewerbung für die Allianz wurde abgeschickt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
