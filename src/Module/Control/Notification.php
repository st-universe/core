<?php

namespace Stu\Module\Control;

final class Notification implements NotificationInterface
{
    private string $text = '';

    /** @var null|string */
    private $link;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NotificationInterface
    {
        $this->text = $text;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): NotificationInterface
    {
        $this->link = $link;
        return $this;
    }
}
