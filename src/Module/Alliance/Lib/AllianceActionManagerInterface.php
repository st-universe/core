<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

interface AllianceActionManagerInterface
{
    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void;

    public function delete(int $allianceId): void;
}