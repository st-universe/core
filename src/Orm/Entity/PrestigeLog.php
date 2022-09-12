<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PrestigeLogRepository")
 * @Table(
 *     name="stu_prestige_log"
 * )
 **/
class PrestigeLog implements PrestigeLogInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $amount = 0;

    /** @Column(type="text") */
    private $description = '';

    /** @Column(type="integer", nullable=true) * */
    private $date;


    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): PrestigeLogInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): PrestigeLogInterface
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): PrestigeLogInterface
    {
        $this->description = $description;
        return $this;
    }

    public function setDate(int $date): PrestigeLogInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }
}
