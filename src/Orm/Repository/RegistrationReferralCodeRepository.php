<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RegistrationReferralCode;

/**
 * @extends EntityRepository<RegistrationReferralCode>
 */
final class RegistrationReferralCodeRepository extends EntityRepository implements RegistrationReferralCodeRepositoryInterface
{
    #[\Override]
    public function prototype(): RegistrationReferralCode
    {
        return new RegistrationReferralCode();
    }

    #[\Override]
    public function getActiveByCode(string $code): ?RegistrationReferralCode
    {
        return $this->findOneBy([
            'code' => $code,
            'active' => true
        ]);
    }

    #[\Override]
    public function incrementHitCount(RegistrationReferralCode $referralCode): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'UPDATE %s r SET r.hit_count = r.hit_count + 1 WHERE r.id = :id',
                RegistrationReferralCode::class
            )
        )
            ->setParameter('id', $referralCode->getId())
            ->execute();
    }

    #[\Override]
    public function save(RegistrationReferralCode $referralCode): void
    {
        $em = $this->getEntityManager();
        $em->persist($referralCode);
        $em->flush();
    }
}
