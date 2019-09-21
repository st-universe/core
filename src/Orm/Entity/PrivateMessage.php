<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\UserRepositoryInterface;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") */
    private $send_user = 0;

    /** @Column(type="integer") */
    private $recip_user = 0;

    /** @Column(type="text") */
    private $text = '';

    /** @Column(type="integer") */
    private $date = 0;

    /** @Column(type="boolean") */
    private $new = false;

    /** @Column(type="boolean") */
    private $replied = false;

    /** @Column(type="integer") */
    private $cat_id = 0;

    /**
     * @ManyToOne(targetEntity="PrivateMessageFolder")
     * @JoinColumn(name="cat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSenderId(): int
    {
        return $this->send_user;
    }

    public function setSenderId(int $senderId): PrivateMessageInterface
    {
        $this->send_user = $senderId;
        return $this;
    }

    public function getRecipientId(): int
    {
        return $this->recip_user;
    }

    public function setRecipientId(int $recipientId): PrivateMessageInterface
    {
        $this->recip_user = $recipientId;
        return $this;
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
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getSenderId());
    }

    public function getRecipient(): UserInterface
    {
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getRecipientId());
    }

}
