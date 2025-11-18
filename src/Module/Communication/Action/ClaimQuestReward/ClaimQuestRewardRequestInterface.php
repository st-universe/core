<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ClaimQuestReward;

interface ClaimQuestRewardRequestInterface
{
    public function getQuestId(): int;

    public function getColonyId(): int;
}
