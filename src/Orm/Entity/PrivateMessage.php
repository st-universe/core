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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\PrivateMessageRepository;

#[Table(name: 'stu_pms')]
#[Index(name: 'recipient_folder_idx', columns: ['recip_user', 'cat_id'])]
#[Index(name: 'correspondence', columns: ['recip_user', 'send_user'])]
#[Index(name: 'pm_date_idx', columns: ['date'])]
#[Entity(repositoryClass: PrivateMessageRepository::class)]
#[TruncateOnGameReset]
class PrivateMessage
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

    #[Column(type: 'integer')]
    private int $cat_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $inbox_pm_id = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $href = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $former_send_user = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $former_recip_user = null;

    #[ManyToOne(targetEntity: PrivateMessageFolder::class)]
    #[JoinColumn(name: 'cat_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PrivateMessageFolder $category;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'send_user', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $sendingUser;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recip_user', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $receivingUser;

    #[OneToOne(targetEntity: PrivateMessage::class, inversedBy: 'outboxPm')]
    #[JoinColumn(name: 'inbox_pm_id', referencedColumnName: 'id')]
    private ?PrivateMessage $inboxPm = null;

    #[OneToOne(targetEntity: PrivateMessage::class, mappedBy: 'inboxPm')]
    private ?PrivateMessage $outboxPm = null;

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

    public function setText(string $text): PrivateMessage
    {
        $this->text = $text;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): PrivateMessage
    {
        $this->date = $date;
        return $this;
    }

    public function getNew(): bool
    {
        return $this->new;
    }

    public function setNew(bool $new): PrivateMessage
    {
        $this->new = $new;
        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->cat_id;
    }

    public function getInboxPm(): ?PrivateMessage
    {
        return $this->inboxPm;
    }

    public function setInboxPm(?PrivateMessage $pm): PrivateMessage
    {
        $this->inboxPm = $pm;
        return $this;
    }

    public function getOutboxPm(): ?PrivateMessage
    {
        return $this->outboxPm;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHref(?string $href): PrivateMessage
    {
        $this->href = $href;
        return $this;
    }

    public function getCategory(): PrivateMessageFolder
    {
        return $this->category;
    }

    public function setCategory(PrivateMessageFolder $folder): PrivateMessage
    {
        $this->category = $folder;
        return $this;
    }

    public function getSender(): User
    {
        return $this->sendingUser;
    }

    public function setSender(User $user): PrivateMessage
    {
        $this->sendingUser = $user;
        return $this;
    }

    public function getRecipient(): User
    {
        return $this->receivingUser;
    }

    public function setRecipient(User $recipient): PrivateMessage
    {
        $this->receivingUser = $recipient;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    public function setDeleted(int $timestamp): PrivateMessage
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }

    public function getFormerSendUser(): ?int
    {
        return $this->former_send_user;
    }

    public function setFormerSendUser(?int $former_send_user): PrivateMessage
    {
        $this->former_send_user = $former_send_user;
        return $this;
    }

    public function getFormerRecipUser(): ?int
    {
        return $this->former_recip_user;
    }

    public function setFormerRecipUser(?int $former_recip_user): PrivateMessage
    {
        $this->former_recip_user = $former_recip_user;
        return $this;
    }
}
