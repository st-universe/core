<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_prestige_log')]
#[Index(name: 'prestige_log_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\PrestigeLogRepository')]
class PrestigeLog implements PrestigeLogInterface
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setUserId(int $userId): PrestigeLogInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->amount;
    }

    #[Override]
    public function setAmount(int $amount): PrestigeLogInterface
    {
        $this->amount = $amount;
        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): PrestigeLogInterface
    {
        $this->description = $description;
        return $this;
    }

    #[Override]
    public function setDate(int $date): PrestigeLogInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }
}
