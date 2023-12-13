<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_user_iptable')]
#[Index(name: 'session_idx', columns: ['session'])]
#[Index(name: 'iptable_start_idx', columns: ['startDate'])]
#[Index(name: 'iptable_end_idx', columns: ['endDate'])]
#[Index(name: 'iptable_ip_idx', columns: ['ip'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserIpTableRepository')]
class UserIpTable implements UserIpTableInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'string')]
    private string $ip;

    #[Column(type: 'string')]
    private string $session;

    #[Column(type: 'string')]
    private string $agent;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $startDate = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserInterface
    {
        $this->user = $user;
        return $this->user;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): UserIpTableInterface
    {
        $this->ip = $ip;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->session;
    }

    public function setSessionId(string $sessionId): UserIpTableInterface
    {
        $this->session = $sessionId;

        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->agent;
    }

    public function setUserAgent(string $userAgent): UserIpTableInterface
    {
        $this->agent = $userAgent;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): UserIpTableInterface
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): UserIpTableInterface
    {
        $this->endDate = $endDate;

        return $this;
    }
}
