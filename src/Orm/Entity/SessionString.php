<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\SessionStringRepository;

#[Table(name: 'stu_session_strings')]
#[Index(name: 'session_string_user_idx', columns: ['user_id', 'sess_string'])]
#[Index(name: 'session_string_date_idx', columns: ['date'])]
#[Entity(repositoryClass: SessionStringRepository::class)]
#[TruncateOnGameReset]
class SessionString
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $sess_string = '';

    #[Column(type: 'datetime')]
    private DateTimeInterface $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): SessionString
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getSessionString(): string
    {
        return $this->sess_string;
    }

    public function setSessionString(string $sessionString): SessionString
    {
        $this->sess_string = $sessionString;

        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): SessionString
    {
        $this->date = $date;

        return $this;
    }
}
