<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ClaimQuestReward;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ClaimQuestRewardRequest implements ClaimQuestRewardRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getQuestId(): int
    {
        return $this->queryParameter('questid')->int()->required();
    }

    #[Override]
    public function getColonyId(): int
    {
        return $this->queryParameter('colonyid')->int()->defaultsTo(0);
    }
}
