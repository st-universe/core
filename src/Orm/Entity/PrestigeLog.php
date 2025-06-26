<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\PrestigeLogRepository;

#[Table(name: 'stu_prestige_log')]
#[Index(name: 'prestige_log_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: PrestigeLogRepository::class)]
class PrestigeLog
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $amount = 0;

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): PrestigeLog
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): PrestigeLog
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): PrestigeLog
    {
        $this->description = $description;
        return $this;
    }

    public function setDate(int $date): PrestigeLog
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }
}
