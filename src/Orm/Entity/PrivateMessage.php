<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\PrivateMessageRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_pms')]
#[Index(name: 'recipient_folder_idx', columns: ['recip_user', 'cat_id'])]
#[Index(name: 'correspondence', columns: ['recip_user', 'send_user'])]
#[Entity(repositoryClass: PrivateMessageRepository::class)]
class PrivateMessage implements PrivateMessageInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $send_user = 0;

    #[Column(type: 'integer')]
    private int $recip_user = 0;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'boolean')]
    private bool $new = false;

    #[Column(type: 'boolean')]
    private bool $replied = false;

    #[Column(type: 'integer')]
    private int $cat_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $inbox_pm_id = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $href = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: 'PrivateMessageFolder')]
    #[JoinColumn(name: 'cat_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PrivateMessageFolderInterface $category;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'send_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $sendingUser;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'recip_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $receivingUser;

    #[OneToOne(targetEntity: 'PrivateMessage', inversedBy: 'outboxPm')]
    #[JoinColumn(name: 'inbox_pm_id', referencedColumnName: 'id')]
    private ?PrivateMessageInterface $inboxPm = null;

    #[OneToOne(targetEntity: 'PrivateMessage', mappedBy: 'inboxPm')]
    private ?PrivateMessageInterface $outboxPm = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSenderId(): int
    {
        return $this->send_user;
    }

    #[Override]
    public function getRecipientId(): int
    {
        return $this->recip_user;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): PrivateMessageInterface
    {
        $this->text = $text;
        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): PrivateMessageInterface
    {
        $this->date = $date;
        return $this;
    }

    #[Override]
    public function getNew(): bool
    {
        return $this->new;
    }

    #[Override]
    public function setNew(bool $new): PrivateMessageInterface
    {
        $this->new = $new;
        return $this;
    }

    #[Override]
    public function getReplied(): bool
    {
        return $this->replied;
    }

    #[Override]
    public function setReplied(bool $replied): PrivateMessageInterface
    {
        $this->replied = $replied;
        return $this;
    }

    #[Override]
    public function getCategoryId(): int
    {
        return $this->cat_id;
    }

    #[Override]
    public function setCategoryId(int $categoryId): PrivateMessageInterface
    {
        $this->cat_id = $categoryId;
        return $this;
    }

    #[Override]
    public function getInboxPm(): ?PrivateMessageInterface
    {
        return $this->inboxPm;
    }

    #[Override]
    public function setInboxPm(?PrivateMessageInterface $pm): PrivateMessageInterface
    {
        $this->inboxPm = $pm;
        return $this;
    }

    #[Override]
    public function getOutboxPm(): ?PrivateMessageInterface
    {
        return $this->outboxPm;
    }

    #[Override]
    public function getHref(): ?string
    {
        return $this->href;
    }

    #[Override]
    public function setHref(?string $href): PrivateMessageInterface
    {
        $this->href = $href;
        return $this;
    }

    #[Override]
    public function getCategory(): PrivateMessageFolderInterface
    {
        return $this->category;
    }

    #[Override]
    public function setCategory(PrivateMessageFolderInterface $folder): PrivateMessageInterface
    {
        $this->category = $folder;
        return $this;
    }

    #[Override]
    public function getSender(): UserInterface
    {
        return $this->sendingUser;
    }

    #[Override]
    public function setSender(UserInterface $user): PrivateMessageInterface
    {
        $this->sendingUser = $user;
        return $this;
    }

    #[Override]
    public function getRecipient(): UserInterface
    {
        return $this->receivingUser;
    }

    #[Override]
    public function setRecipient(UserInterface $recipient): PrivateMessageInterface
    {
        $this->receivingUser = $recipient;
        return $this;
    }

    #[Override]
    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    #[Override]
    public function setDeleted(int $timestamp): PrivateMessageInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    #[Override]
    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
