<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Create\Create;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateAlliance implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_ALLIANCE';

    public function __construct(
        private CreateAllianceRequestInterface $createAllianceRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $name = $this->createAllianceRequest->getName();
        $faction_mode = $this->createAllianceRequest->getFactionMode();
        $description = $this->createAllianceRequest->getDescription();

        if (mb_strlen($name) < 5) {
            $game->setView(Create::VIEW_IDENTIFIER);
            $game->getInfo()->addInformation('Der Name muss aus mindestens 5 Zeichen bestehen');
            return;
        }

        $alliance = $this->allianceRepository->prototype();
        $alliance->setName($name);
        $alliance->setDescription($description);
        $alliance->setDate(time());
        if ($faction_mode === 1) {
            $alliance->setFaction($user->getFaction());
        }

        $this->allianceRepository->save($alliance);
        $this->entityManager->flush();

        $founderJob = new AllianceJob();
        $founderJob->setAlliance($alliance);
        $founderJob->setTitle('Präsident');
        $founderJob->setSort(1);
        $founderJob->setIsFounderPermission(true);
        $founderJob->setIsSuccessorPermission(true);
        $founderJob->setIsDiplomaticPermission(true);

        $this->allianceJobRepository->save($founderJob);
        $this->entityManager->flush();

        $user->setAlliance($alliance);
        $this->userRepository->save($user);

        $this->allianceJobManager->assignUserToJob($user, $founderJob);
        $this->entityManager->flush();

        $this->entityManager->refresh($alliance);

        $game->getInfo()->addInformation('Die Allianz wurde gegründet');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
