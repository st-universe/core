<?php

declare(strict_types=1);

namespace Stu\Module\Prestige\Lib;

use Override;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreatePrestigeLog implements CreatePrestigeLogInterface
{
    public function __construct(private PrestigeLogRepositoryInterface $prestigeLogRepository, private UserRepositoryInterface $userRepository, private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
    public function createLog(int $amount, string $description, UserInterface $user, int $date): void
    {
        $this->createLogIntern($amount, $description, $user, $date);
    }

    #[Override]
    public function createLogForDatabaseEntry(DatabaseEntryInterface $databaseEntry, UserInterface $user, int $date): void
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

    private function createLogIntern(int $amount, string $description, UserInterface $user, int $date): void
    {
        $prestigeLog = $this->prestigeLogRepository->prototype();
        $prestigeLog->setUserId($user->getId());
        $prestigeLog->setAmount($amount);
        $prestigeLog->setDate($date);
        $prestigeLog->setDescription($description);

        $this->prestigeLogRepository->save($prestigeLog);

        //update user prestige
        $user->setPrestige($user->getPrestige() + $prestigeLog->getAmount());
        $this->userRepository->save($user);

        $this->componentLoader->addComponentUpdate(ComponentEnum::USER_NAVLET);
    }
}
