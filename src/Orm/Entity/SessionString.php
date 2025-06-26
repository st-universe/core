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
use Stu\Orm\Repository\SessionStringRepository;

#[Table(name: 'stu_session_strings')]
#[Index(name: 'session_string_user_idx', columns: ['sess_string', 'user_id'])]
#[Index(name: 'session_string_date_idx', columns: ['date'])]
#[Entity(repositoryClass: SessionStringRepository::class)]
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

    #[Column(type: 'datetime', nullable: true)]
    private DateTimeInterface $date;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(User $user): SessionString
    {
        $this->user = $user;
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
