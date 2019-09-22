<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
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
        return $this->opponent;
    }

    public function setRecipient(UserInterface $recipient): IgnoreListInterface
    {
        $this->opponent = $recipient;
        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): IgnoreListInterface
    {
        $this->user = $user;
        return $this;
    }
}
