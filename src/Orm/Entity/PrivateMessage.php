<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use User;

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

    private $senderignore;

    private $sendercontact;

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

    public function isMarkableAsNew(): bool
    {
        if ($this->getNew() === false) {
            return false;
        }
        $this->setNew(false);

        // @todo refactor
        global $container;

        $privateMessageRepo = $container->get(PrivateMessageRepositoryInterface::class);
        $privateMessageRepo->save($this);

        return true;
    }

    public function getSender(): User
    {
        return new User($this->getSenderId());
    }

    public function getRecipient(): User
    {
        return new User($this->getRecipientId());
    }

    public function copyPM(): void
    {
        // @todo refactor
        global $container;

        $privateMessageFolderRepo = $container->get(PrivateMessageFolderRepositoryInterface::class);
        $folder = $privateMessageFolderRepo->getByUserAndSpecial($this->getSenderId(), PM_SPECIAL_PMOUT);

        $newobj = clone($this);
        $newobj->setSenderId($this->getRecipientId());
        $newobj->setRecipientId($this->getSenderId());
        $newobj->setCategory($folder);
        $newobj->setNew(false);

        $privateMessageRepo = $container->get(PrivateMessageRepositoryInterface::class);
        $privateMessageRepo->save($newobj);
    }

    public function senderIsIgnored(): bool
    {
        if ($this->senderignore === null) {
            // @todo refactor
            global $container;

            $this->senderignore = $container->get(IgnoreListRepositoryInterface::class)->exists(
                currentUser()->getId(),
                (int)$this->getSenderId()
            );
        }
        return $this->senderignore;
    }

    public function senderIsContact(): ?ContactInterface
    {
        if ($this->sendercontact === null) {
            // @todo refactor
            global $container;

            $this->sendercontact = $container->get(ContactRepositoryInterface::class)
                ->getByUserAndOpponent(
                    currentUser()->getId(),
                    (int)$this->getSenderId()
                );
        }
        return $this->sendercontact;
    }

    public function displayUserLinks(): bool
    {
        return $this->getSender() && $this->getSender()->getId() !== USER_NOONE;
    }

}
