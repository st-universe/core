<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserSetting;

/**
 * @extends EntityRepository<UserSetting>
 */
final class UserSettingRepository extends EntityRepository implements UserSettingRepositoryInterface
{
    #[\Override]
    public function prototype(): UserSetting
    {
        return new UserSetting();
    }

    #[\Override]
    public function save(UserSetting $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->persist($userSetting);
    }

    #[\Override]
    public function delete(UserSetting $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->remove($userSetting);
    }

    #[\Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s us WHERE us.user = :user',
                UserSetting::class
            )
        )->setParameters([
            'user' => $user,
        ])->execute();
    }
}
