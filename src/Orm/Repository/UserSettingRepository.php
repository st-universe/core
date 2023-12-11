<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Entity\UserSettingInterface;

/**
 * @extends EntityRepository<UserSetting>
 */
final class UserSettingRepository extends EntityRepository implements UserSettingRepositoryInterface
{
    public function prototype(): UserSettingInterface
    {
        return new UserSetting();
    }

    public function save(UserSettingInterface $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->persist($userSetting);
    }

    public function delete(UserSettingInterface $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->remove($userSetting);
    }

    public function truncateByUser(UserInterface $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s us WHERE us.user_id = :userId',
                UserSetting::class
            )
        )->setParameters([
            'userId' => $user->getId(),
        ])->execute();
    }
}
