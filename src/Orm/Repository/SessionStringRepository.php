<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\SessionString;
use Stu\Orm\Entity\SessionStringInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<SessionString>
 */
final class SessionStringRepository extends EntityRepository implements SessionStringRepositoryInterface
{
    #[Override]
    public function isValid(string $sessionString, int $userId): bool
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s t WHERE t.user_id = :userId and t.sess_string = :sessionString',
                SessionString::class,
            )
        );
        $q->setParameters([
            'userId' => $userId,
            'sessionString' => $sessionString,
        ]);
        return $q->execute() > 0;
    }

    #[Override]
    public function truncate(UserInterface $user): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.user_id = :user OR t.date < :date',
                SessionString::class,
            )
        );
        $q->setParameters([
            'user' => $user,
            'date' => (new DateTime())->sub(new DateInterval('PT1H'))->format('Y-m-d H:i:s'),
        ]);
        $q->execute();
    }

    #[Override]
    public function prototype(): SessionStringInterface
    {
        return new SessionString();
    }

    #[Override]
    public function save(SessionStringInterface $sessionString): void
    {
        $em = $this->getEntityManager();

        $em->persist($sessionString);
        $em->flush();
    }
}
