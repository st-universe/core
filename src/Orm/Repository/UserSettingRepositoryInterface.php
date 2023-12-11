<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Entity\UserSettingInterface;

/**
 * @extends ObjectRepository<UserSetting>
 */
interface UserSettingRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserSettingInterface;

    public function save(UserSettingInterface $userSetting): void;

    public function delete(UserSettingInterface $userSetting): void;

    public function truncateByUser(UserInterface $user): void;
}
