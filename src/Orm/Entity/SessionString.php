<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\SessionStringRepository")
 * @Table(
 *     name="stu_session_strings",
 *     indexes={
 *          @Index(name="session_string_user_idx", columns={"sess_string", "user_id"}),
 *          @Index(name="session_string_date_idx", columns={"date"})
 *     }
 * )
 **/
class SessionString implements SessionStringInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="string") * */
    private $sess_string;

    /** @Column(type="datetime", nullable=true) * */
    private $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): SessionStringInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getSessionString(): string {
        return $this->sess_string;
    }

    public function setSessionString(string $sessionString): SessionStringInterface {
        $this->sess_string = $sessionString;

        return $this;
    }

    public function getDate(): DateTimeInterface {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): SessionStringInterface {
        $this->date = $date;

        return $this;
    }
}
