<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use AccessViolation;
use Alliance;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class Signup implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    private $signupRequest;

    private $allianceJobRepository;

    public function __construct(
        SignupRequestInterface $signupRequest,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->signupRequest = $signupRequest;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = new Alliance($this->signupRequest->getAllianceId());
        $allianceId = (int) $alliance->getId();

        if (!$user->maySignup($allianceId)) {
            throw new AccessViolation();
        }
        $obj = $this->allianceJobRepository->prototype();
        $obj->setUserId($userId);
        $obj->setType(ALLIANCE_JOBS_PENDING);
        $obj->setAllianceId($allianceId);

        $this->allianceJobRepository->save($obj);

        $text = sprintf(
            'Der Siedler %s hat sich für die Allianz beworben',
            $user->getName()
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
