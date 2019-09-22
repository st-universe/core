<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserIpTableRepository")
 * @Table(name="stu_user_iptable",indexes={@Index(name="session_idx", columns={"session"})})
 **/
class UserIpTable implements UserIpTableInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="string") * */
    private $ip;

    /** @Column(type="string") * */
    private $session;

    /** @Column(type="string") * */
    private $agent;

    /** @Column(type="datetime", nullable=true) * */
    private $start;

    /** @Column(type="datetime", nullable=true) * */
    private $end;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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

    public function getSessionId(): string {
        return $this->session;
    }

    public function setSessionId(string $sessionId): UserIpTableInterface {
        $this->session = $sessionId;

        return $this;
    }

    public function getUserAgent(): string {
        return $this->agent;
    }

    public function setUserAgent(string $userAgent): UserIpTableInterface {
        $this->agent = $userAgent;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface {
        return $this->start;
    }

    public function setStartDate(DateTimeInterface $startDate): UserIpTableInterface {
        $this->start = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface {
        return $this->end;
    }

    public function setEndDate(DateTimeInterface $endDate): UserIpTableInterface {
        $this->end = $endDate;

        return $this;
    }
}
