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
