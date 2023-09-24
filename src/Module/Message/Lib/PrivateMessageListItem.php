<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PrivateMessageListItem implements PrivateMessageListItemInterface
{
    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private ContactRepositoryInterface $contactRepository;

    private IgnoreListRepositoryInterface $ignoreListRepository;

    private PrivateMessageInterface $message;

    private int $currentUserId;

    private ?UserInterface $sender = null;

    private ?bool $senderignore = null;

    private ?ContactInterface $sendercontact = null;

    public function __construct(
        PrivateMessageRepositoryInterface $privateMessageRepository,
        ContactRepositoryInterface $contactRepository,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageInterface $message,
        int $currentUserId
    ) {
        $this->privateMessageRepository = $privateMessageRepository;
        $this->contactRepository = $contactRepository;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->message = $message;
        $this->currentUserId = $currentUserId;
    }

    public function getSender(): UserInterface
    {
        if ($this->sender === null) {
            $this->sender = $this->message->getSender();
        }
        return $this->sender;
    }

    public function getDate(): int
    {
        return $this->message->getDate();
    }

    public function isMarkableAsNew(): bool
    {
        if ($this->message->getNew() === false) {
            return false;
        }
        $this->message->setNew(false);

        $this->privateMessageRepository->save($this->message);

        return true;
    }

    public function isMarkableAsReceipt(): bool
    {
        if ($this->message->getInboxPmId() === null) {
            return false;
        }

        if (!$this->message->getSender()->isShowPmReadReceipt() || !$this->message->getRecipient()->isShowPmReadReceipt()) {
            return false;
        }

        $inboxPm = $this->privateMessageRepository->find($this->message->getInboxPmId());

        if ($inboxPm === null) {
            return true;
        }

        return !$inboxPm->getNew();
    }

    public function getText(): string
    {
        return $this->message->getText();
    }

    public function getHref(): ?string
    {
        return $this->message->getHref();
    }

    public function getNew(): bool
    {
        return $this->message->getNew();
    }

    public function getId(): int
    {
        return $this->message->getId();
    }

    public function displayUserLinks(): bool
    {
        return $this->getSender() && $this->getSender()->getId() !== UserEnum::USER_NOONE;
    }

    public function getReplied(): bool
    {
        return $this->message->getReplied();
    }

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

    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
