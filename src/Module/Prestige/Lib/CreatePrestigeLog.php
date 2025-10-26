<?php

declare(strict_types=1);

namespace Stu\Module\Prestige\Lib;

use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreatePrestigeLog implements CreatePrestigeLogInterface
{
    public function __construct(
        private PrestigeLogRepositoryInterface $prestigeLogRepository,
        private UserRepositoryInterface $userRepository,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[\Override]
    public function createLog(int $amount, string $description, User $user, int $date): void
    {
        $this->createLogIntern($amount, $description, $user, $date);
    }

    #[\Override]
    public function createLogForDatabaseEntry(DatabaseEntry $databaseEntry, User $user, int $date): void
    {
        $amount = $databaseEntry->getCategory()->getPrestige();
        $description = sprintf(
            '%d Prestige erhalten für die Entdeckung von "%s" in der Kategorie "%s"',
            $amount,
            $databaseEntry->getDescription(),
            $databaseEntry->getCategory()->getDescription()
        );

        $this->createLogIntern($amount, $description, $user, $date);
    }

    private function createLogIntern(int $amount, string $description, User $user, int $date): void
    {
        if ($user->getId() < UserConstants::USER_FIRST_ID) {
            return;
        }

        $prestigeLog = $this->prestigeLogRepository->prototype();
        $prestigeLog->setUserId($user->getId());
        $prestigeLog->setAmount($amount);
        $prestigeLog->setDate($date);
        $prestigeLog->setDescription($description);

        $this->prestigeLogRepository->save($prestigeLog);

        //update user prestige
        $user->setPrestige($user->getPrestige() + $prestigeLog->getAmount());
        $this->userRepository->save($user);

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::USER);
    }
}
