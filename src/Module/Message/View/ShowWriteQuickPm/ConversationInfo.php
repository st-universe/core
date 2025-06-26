<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use JBBCode\Parser;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

class ConversationInfo
{
    /**
     * @var User|Spacecraft|Fleet|Colony
     */
    private $from;

    /**
     * @var User|Spacecraft|Fleet|Colony
     */
    private $to;

    private bool $showTemplateText = true;
    private string $whoText;
    private string $sectorString;
    private string $toText;
    private User $recipient;


    /**
     * @param User|Spacecraft|Fleet|Colony $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * @param User|Spacecraft|Fleet|Colony $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    public function setShowTemplateText(bool $showTemplateText): void
    {
        $this->showTemplateText = $showTemplateText;
    }

    public function setWhoText(string $whoText): void
    {
        $this->whoText = $whoText;
    }

    public function setSectorString(string $sectorString): void
    {
        $this->sectorString = $sectorString;
    }

    public function setToText(string $toText): void
    {
        $this->toText = $toText;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function getTemplateText(Parser $bbCodeParser): string
    {
        if ($this->showTemplateText) {
            return sprintf(
                _('%s "%s" sendet %s "%s" in Sektor %s folgende Nachricht:'),
                $this->whoText,
                $bbCodeParser->parse($this->from->getName())->getAsText(),
                $this->toText,
                $bbCodeParser->parse($this->to->getName())->getAsText(),
                $this->sectorString
            );
        }

        return '';
    }
}
