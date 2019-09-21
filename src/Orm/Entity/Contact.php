<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $recipient = 0;

    /** @Column(type="smallint") */
    private $mode = 0;

    /** @Column(type="string", length=50) */
    private $comment = '';

    /** @Column(type="integer") */
    private $date = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ContactInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getRecipientId(): int
    {
        return $this->recipient;
    }

    public function setRecipientId(int $recipientId): ContactInterface
    {
        $this->recipient = $recipientId;
        return $this;
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
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getRecipientId());
    }

    public function getUser(): UserInterface
    {
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getUserId());
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
