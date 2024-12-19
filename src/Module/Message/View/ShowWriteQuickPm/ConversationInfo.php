<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use JBBCode\Parser;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class ConversationInfo
{
    /**
     * @var UserInterface|SpacecraftInterface|FleetInterface|ColonyInterface
     */
    private $from;

    /**
     * @var UserInterface|SpacecraftInterface|FleetInterface|ColonyInterface
     */
    private $to;

    private bool $showTemplateText = true;
    private string $whoText;
    private string $sectorString;
    private string $toText;
    private UserInterface $recipient;


    /**
     * @param UserInterface|SpacecraftInterface|FleetInterface|ColonyInterface $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * @param UserInterface|SpacecraftInterface|FleetInterface|ColonyInterface $to
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

    public function getRecipient(): UserInterface
    {
        return $this->recipient;
    }

    public function setRecipient(UserInterface $recipient): void
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
