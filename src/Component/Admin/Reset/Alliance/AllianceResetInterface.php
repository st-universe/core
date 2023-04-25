<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Alliance;

interface AllianceResetInterface
{
    public function deleteAllAllianceBoards(): void;

    public function deleteAllUserAllianceJobs(): void;

    public function deleteAllUserAllianceRelations(): void;

    public function deleteAllUserAlliances(): void;
}
