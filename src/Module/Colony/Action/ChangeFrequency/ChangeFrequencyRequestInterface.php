<?php

namespace Stu\Module\Colony\Action\ChangeFrequency;

interface ChangeFrequencyRequestInterface
{
    public function getFrequency(): int;
}
