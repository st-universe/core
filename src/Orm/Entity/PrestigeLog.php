<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PrestigeLogRepository")
 * @Table(
 *     name="stu_prestige_log",
 *     indexes={
 *         @Index(name="prestige_log_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class PrestigeLog implements PrestigeLogInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $amount = 0;

    /**
     * @Column(type="text")
     *
     * @var string
     */
    private $description = '';

    /**
     * @Column(type="integer")
     *
     * @var int
     */
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
