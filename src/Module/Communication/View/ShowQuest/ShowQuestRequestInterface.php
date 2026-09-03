<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuest;

interface ShowQuestRequestInterface
{
    public function getQuestId(): ?int;
}
