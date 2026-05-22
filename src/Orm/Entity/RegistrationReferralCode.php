<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\RegistrationReferralCodeRepository;

#[Table(name: 'stu_registration_referral_code')]
#[Entity(repositoryClass: RegistrationReferralCodeRepository::class)]
class RegistrationReferralCode
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 30, unique: true)]
    private string $code = '';

    #[Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[Column(type: 'integer')]
    private int $hit_count = 0;

    #[Column(type: 'boolean')]
    private bool $active = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): RegistrationReferralCode
    {
        $this->code = $code;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): RegistrationReferralCode
    {
        $this->description = $description;
        return $this;
    }

    public function getHitCount(): int
    {
        return $this->hit_count;
    }

    public function setHitCount(int $hitCount): RegistrationReferralCode
    {
        $this->hit_count = $hitCount;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): RegistrationReferralCode
    {
        $this->active = $active;
        return $this;
    }
}
