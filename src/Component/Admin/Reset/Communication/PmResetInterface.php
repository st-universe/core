<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

interface PmResetInterface
{
    public function resetAllNonNpcPmFolders(): void;

    public function resetPms(): void;

    public function deleteAllContacts(): void;
}
