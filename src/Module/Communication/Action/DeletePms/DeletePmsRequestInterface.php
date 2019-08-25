<?php

namespace Stu\Module\Communication\Action\DeletePms;

interface DeletePmsRequestInterface
{
    public function getIgnoreIds(): array;
}