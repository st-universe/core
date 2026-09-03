<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ApplyForQuest;

interface ApplyForQuestRequestInterface
{
    public function getQuestId(): int;
}
