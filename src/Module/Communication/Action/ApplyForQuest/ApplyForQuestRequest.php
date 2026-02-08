<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ApplyForQuest;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ApplyForQuestRequest implements ApplyForQuestRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getQuestId(): int
    {
        return $this->parameter('questid')->int()->required();
    }
}
