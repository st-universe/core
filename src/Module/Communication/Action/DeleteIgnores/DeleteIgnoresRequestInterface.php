<?php

namespace Stu\Module\Communication\Action\DeleteIgnores;

interface DeleteIgnoresRequestInterface
{
    public function getIgnoreIds(): array;
}