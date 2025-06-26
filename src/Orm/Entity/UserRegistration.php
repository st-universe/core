<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_user_registration')]
#[Entity]
class UserRegistration
{
    #[Id]
    #[OneToOne(targetEntity: User::class, inversedBy: 'registration')]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

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

    #[OneToOne(targetEntity: UserReferer::class, mappedBy: 'userRegistration')]
    private ?UserReferer $referer = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): UserRegistration
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->pass;
    }

    public function setPassword(string $password): UserRegistration
    {
        $this->pass = $password;
        return $this;
    }

    public function getSmsCode(): ?string
    {
        return $this->sms_code;
    }

    public function setSmsCode(?string $code): UserRegistration
    {
        $this->sms_code = $code;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserRegistration
    {
        $this->email = $email;
        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): UserRegistration
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function getCreationDate(): int
    {
        return $this->creation;
    }

    public function setCreationDate(int $creationDate): UserRegistration
    {
        $this->creation = $creationDate;
        return $this;
    }

    public function getDeletionMark(): int
    {
        return $this->delmark;
    }

    public function setDeletionMark(int $deletionMark): UserRegistration
    {
        $this->delmark = $deletionMark;
        return $this;
    }

    public function getPasswordToken(): string
    {
        return $this->password_token;
    }

    public function setPasswordToken(string $password_token): UserRegistration
    {
        $this->password_token = $password_token;
        return $this;
    }

    public function getReferer(): ?UserReferer
    {
        return $this->referer;
    }

    public function setReferer(?UserReferer $referer): UserRegistration
    {
        $this->referer = $referer;
        return $this;
    }

    public function getSmsSended(): int
    {
        return $this->sms_sended ?? 1;
    }

    public function setSmsSended(int $smsSended): UserRegistration
    {
        $this->sms_sended = $smsSended;
        return $this;
    }

    public function getEmailCode(): ?string
    {
        return $this->email_code;
    }

    public function setEmailCode(?string $emailCode): UserRegistration
    {
        $this->email_code = $emailCode;
        return $this;
    }
}
