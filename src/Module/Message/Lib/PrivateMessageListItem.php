<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PrivateMessageListItem implements PrivateMessageListItemInterface
{
    private ?UserInterface $sender = null;

    private ?bool $senderignore = null;

    private ?ContactInterface $sendercontact = null;

    public function __construct(private PrivateMessageRepositoryInterface $privateMessageRepository, private ContactRepositoryInterface $contactRepository, private IgnoreListRepositoryInterface $ignoreListRepository, private PrivateMessageInterface $message, private int $currentUserId)
    {
    }

    #[Override]
    public function getSender(): UserInterface
    {
        if ($this->sender === null) {
            $this->sender = $this->message->getSender();
        }
        return $this->sender;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->message->getDate();
    }

    #[Override]
    public function isMarkableAsNew(): bool
    {
        if ($this->message->getNew() === false) {
            return false;
        }
        $this->message->setNew(false);

        $this->privateMessageRepository->save($this->message, true);

        return true;
    }

    #[Override]
    public function isMarkableAsReceipt(): bool
    {
        $inboxPm = $this->message->getInboxPm();
        if ($inboxPm === null) {
            return false;
        }

        if (
            !$this->message->getSender()->isShowPmReadReceipt()
            || !$this->message->getRecipient()->isShowPmReadReceipt()
        ) {
            return false;
        }

        if ($inboxPm->isDeleted()) {
            return true;
        }

        return !$inboxPm->getNew();
    }

    #[Override]
    public function getText(): string
    {
        return $this->message->getText();
    }

    #[Override]
    public function getHref(): ?string
    {
        return $this->message->getHref();
    }

    #[Override]
    public function getNew(): bool
    {
        return $this->message->getNew();
    }

    #[Override]
    public function getId(): int
    {
        return $this->message->getId();
    }

    #[Override]
    public function displayUserLinks(): bool
    {
        return $this->getSender() && $this->getSender()->getId() !== UserEnum::USER_NOONE;
    }

    #[Override]
    public function getReplied(): bool
    {
        return $this->message->getReplied();
    }

    #[Override]
    public function senderIsIgnored(): bool
    {
        if ($this->senderignore === null) {
            $this->senderignore = $this->ignoreListRepository->exists(
                $this->currentUserId,
                $this->message->getSenderId()
            );
        }
        return $this->senderignore;
    }

    #[Override]
    public function senderIsContact(): ?ContactInterface
    {
        if ($this->sendercontact === null) {
            $this->sendercontact = $this->contactRepository->getByUserAndOpponent(
                $this->currentUserId,
                $this->message->getSenderId()
            );
        }
        return $this->sendercontact;
    }

    #[Override]
    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
