<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\IgnoreListRepository")
 * @Table(
 *     name="stu_ignorelist",
 *     indexes={
 *         @Index(name="user_recipient_idx", columns={"user_id","recipient"})
 *     }
 * )
 **/
class IgnoreList implements IgnoreListInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $recipient = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): IgnoreListInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getRecipientId(): int
    {
        return $this->recipient;
    }

    public function setRecipientId(int $recipientId): IgnoreListInterface
    {
        $this->recipient = $recipientId;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): IgnoreListInterface
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
}
