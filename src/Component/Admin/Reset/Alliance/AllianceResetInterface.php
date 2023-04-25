<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Alliance;

interface AllianceResetInterface
{
    public function deleteAllAllianceBoards(): void;

    public function deleteAllAllianceJobs(): void;

    public function deleteAllAllianceRelations(): void;

    public function deleteAllAlliances(): void;
}
