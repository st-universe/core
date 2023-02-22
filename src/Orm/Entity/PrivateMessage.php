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
 * @Entity(repositoryClass="Stu\Orm\Repository\PrivateMessageRepository")
 * @Table(
 *     name="stu_pms",
 *     indexes={
 *         @Index(name="recipient_folder_idx", columns={"recip_user", "cat_id"}),
 *         @Index(name="correspondence", columns={"recip_user", "send_user"})
 *     }
 * )
 **/
class PrivateMessage implements PrivateMessageInterface
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
    private $send_user = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $recip_user = 0;

    /**
     * @Column(type="text")
     *
     * @var string
     */
    private $text = '';

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $new = false;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $replied = false;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $cat_id = 0;

    /**
     * @Column(type="integer", nullable = true)
     *
     * @var int|null
     */
    private $inbox_pm_id;

    /**
     * @Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $href;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $deleted;

    /**
     * @var PrivateMessageFolderInterface
     *
     * @ManyToOne(targetEntity="PrivateMessageFolder")
     * @JoinColumn(name="cat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="send_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $sendingUser;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="recip_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $receivingUser;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSenderId(): int
    {
        return $this->send_user;
    }

    public function getRecipientId(): int
    {
        return $this->recip_user;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): PrivateMessageInterface
    {
        $this->text = $text;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): PrivateMessageInterface
    {
        $this->date = $date;
        return $this;
    }

    public function getNew(): bool
    {
        return $this->new;
    }

    public function setNew(bool $new): PrivateMessageInterface
    {
        $this->new = $new;
        return $this;
    }

    public function getReplied(): bool
    {
        return $this->replied;
    }

    public function setReplied(bool $replied): PrivateMessageInterface
    {
        $this->replied = $replied;
        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->cat_id;
    }

    public function setCategoryId(int $categoryId): PrivateMessageInterface
    {
        $this->cat_id = $categoryId;
        return $this;
    }

    public function getInboxPmId(): ?int
    {
        return $this->inbox_pm_id;
    }

    public function setInboxPmId(?int $pmId): PrivateMessageInterface
    {
        $this->inbox_pm_id = $pmId;
        return $this;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHref(?string $href): PrivateMessageInterface
    {
        $this->href = $href;
        return $this;
    }

    public function getCategory(): PrivateMessageFolderInterface
    {
        return $this->category;
    }

    public function setCategory(PrivateMessageFolderInterface $folder): PrivateMessageInterface
    {
        $this->category = $folder;
        return $this;
    }

    public function getSender(): UserInterface
    {
        return $this->sendingUser;
    }

    public function setSender(UserInterface $user): PrivateMessageInterface
    {
        $this->sendingUser = $user;
        return $this;
    }

    public function getRecipient(): UserInterface
    {
        return $this->receivingUser;
    }

    public function setRecipient(UserInterface $recipient): PrivateMessageInterface
    {
        $this->receivingUser = $recipient;
        return $this;
    }

    public function setDeleted(int $timestamp): PrivateMessageInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }
}
