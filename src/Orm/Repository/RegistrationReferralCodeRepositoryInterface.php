<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RegistrationReferralCode;

/**
 * @extends ObjectRepository<RegistrationReferralCode>
 */
interface RegistrationReferralCodeRepositoryInterface extends ObjectRepository
{
    public function prototype(): RegistrationReferralCode;

    public function getActiveByCode(string $code): ?RegistrationReferralCode;

    public function incrementHitCount(RegistrationReferralCode $referralCode): void;

    public function save(RegistrationReferralCode $referralCode): void;
}
