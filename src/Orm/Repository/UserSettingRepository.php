<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Entity\UserSettingInterface;

/**
 * @extends EntityRepository<UserSetting>
 */
final class UserSettingRepository extends EntityRepository implements UserSettingRepositoryInterface
{
    #[Override]
    public function prototype(): UserSettingInterface
    {
        return new UserSetting();
    }

    #[Override]
    public function save(UserSettingInterface $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->persist($userSetting);
    }

    #[Override]
    public function delete(UserSettingInterface $userSetting): void
    {
        $em = $this->getEntityManager();

        $em->remove($userSetting);
    }

    #[Override]
    public function truncateByUser(UserInterface $user): void
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
