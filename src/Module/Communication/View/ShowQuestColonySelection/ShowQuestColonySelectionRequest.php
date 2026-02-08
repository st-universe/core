<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuestColonySelection;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowQuestColonySelectionRequest implements ShowQuestColonySelectionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getQuestId(): ?int
    {
        return $this->parameter('questid')->int()->defaultsTo(null);
    }
}
