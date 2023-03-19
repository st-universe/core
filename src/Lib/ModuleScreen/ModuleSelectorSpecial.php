<?php

namespace Stu\Lib\ModuleScreen;

final class ModuleSelectorSpecial extends ModuleSelector
{
    private int $dummyId;

    public function allowMultiple(): bool
    {
        return true;
    }

    public function setDummyId(int $dummyId): void
    {
        $this->dummyId = $dummyId;
    }

    public function getDummyId(): int
    {
        return $this->dummyId;
    }
}
