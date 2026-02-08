<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuest;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowQuestRequest implements ShowQuestRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getQuestId(): ?int
    {
        return $this->parameter('questid')->int()->defaultsTo(null);
    }
}
