<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

interface PmResetInterface
{
    public function unsetAllInboxReferences(): void;

    public function resetAllNonNpcPmFolders(): void;
}
