<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserSetting;

/**
 * @extends ObjectRepository<UserSetting>
 */
interface UserSettingRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserSetting;

    public function save(UserSetting $userSetting): void;

    public function delete(UserSetting $userSetting): void;

    public function truncateByUser(User $user): void;
}
