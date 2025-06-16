<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_user_registration')]
#[Entity]
class UserRegistration implements UserRegistrationInterface
{
    #[Id]
    #[OneToOne(targetEntity: 'User', inversedBy: 'registration')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Column(type: 'string', length: 20)]
    private string $login = '';

    #[Column(type: 'string', length: 255)]
    private string $pass = '';

    #[Column(type: 'string', length: 6, nullable: true)]
    private ?string $sms_code = null;

    #[Column(type: 'string', length: 200)]
    private string $email = '';

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $mobile = null;

    #[Column(type: 'integer')]
    private int $creation = 0;

    #[Column(type: 'smallint')]
    private int $delmark = 0;

    #[Column(type: 'string', length: 255)]
    private string $password_token = '';

    #[Column(type: 'integer', nullable: true, options: ['default' => 1])]
    private ?int $sms_sended = 1;

    #[Column(type: 'string', length: 6, nullable: true)]
    private ?string $email_code = null;

    #[OneToOne(targetEntity: 'UserReferer', mappedBy: 'userRegistration')]
    private ?UserRefererInterface $referer = null;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    #[Override]
    public function getLogin(): string
    {
        return $this->login;
    }

    #[Override]
    public function setLogin(string $login): UserRegistrationInterface
    {
        $this->login = $login;
        return $this;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->pass;
    }

    #[Override]
    public function setPassword(string $password): UserRegistrationInterface
    {
        $this->pass = $password;
        return $this;
    }

    #[Override]
    public function getSmsCode(): ?string
    {
        return $this->sms_code;
    }

    #[Override]
    public function setSmsCode(?string $code): UserRegistrationInterface
    {
        $this->sms_code = $code;
        return $this;
    }

    #[Override]
    public function getEmail(): string
    {
        return $this->email;
    }

    #[Override]
    public function setEmail(string $email): UserRegistrationInterface
    {
        $this->email = $email;
        return $this;
    }

    #[Override]
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    #[Override]
    public function setMobile(?string $mobile): UserRegistrationInterface
    {
        $this->mobile = $mobile;
        return $this;
    }

    #[Override]
    public function getCreationDate(): int
    {
        return $this->creation;
    }

    #[Override]
    public function setCreationDate(int $creationDate): UserRegistrationInterface
    {
        $this->creation = $creationDate;
        return $this;
    }

    #[Override]
    public function getDeletionMark(): int
    {
        return $this->delmark;
    }

    #[Override]
    public function setDeletionMark(int $deletionMark): UserRegistrationInterface
    {
        $this->delmark = $deletionMark;
        return $this;
    }

    #[Override]
    public function getPasswordToken(): string
    {
        return $this->password_token;
    }

    #[Override]
    public function setPasswordToken(string $password_token): UserRegistrationInterface
    {
        $this->password_token = $password_token;
        return $this;
    }

    #[Override]
    public function getReferer(): ?UserRefererInterface
    {
        return $this->referer;
    }

    #[Override]
    public function setReferer(?UserRefererInterface $referer): UserRegistrationInterface
    {
        $this->referer = $referer;
        return $this;
    }

    #[Override]
    public function getSmsSended(): int
    {
        return $this->sms_sended ?? 1;
    }

    #[Override]
    public function setSmsSended(int $smsSended): UserRegistrationInterface
    {
        $this->sms_sended = $smsSended;
        return $this;
    }

    #[Override]
    public function getEmailCode(): ?string
    {
        return $this->email_code;
    }

    #[Override]
    public function setEmailCode(?string $emailCode): UserRegistrationInterface
    {
        $this->email_code = $emailCode;
        return $this;
    }
}
