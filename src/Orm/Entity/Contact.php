<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Message\Lib\ContactListModeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ContactRepository")
 * @Table(
 *     name="stu_contactlist",
 *     indexes={
 *         @Index(name="user_pair_idx", columns={"user_id", "recipient"})
 *     }
 * )
 **/
class Contact implements ContactInterface
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
    private $recipient = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $mode = 0;

    /**
     * @Column(type="string", length=50)
     *
     * @var string
     */
    private $comment = '';

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="recipient", referencedColumnName="id", onDelete="CASCADE")
     */
    private $opponent;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getRecipientId(): int
    {
        return $this->recipient;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): ContactInterface
    {
        $this->mode = $mode;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): ContactInterface
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): ContactInterface
    {
        $this->date = $date;
        return $this;
    }

    public function getRecipient(): UserInterface
    {
        return $this->opponent;
    }

    public function setRecipient(UserInterface $recipient): ContactInterface
    {
        $this->opponent = $recipient;
        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ContactInterface
    {
        $this->user = $user;
        return $this;
    }

    public function isFriendly(): bool
    {
        return $this->getMode() === ContactListModeEnum::CONTACT_FRIEND;
    }

    public function isEnemy(): bool
    {
        return $this->getMode() === ContactListModeEnum::CONTACT_ENEMY;
    }
}
