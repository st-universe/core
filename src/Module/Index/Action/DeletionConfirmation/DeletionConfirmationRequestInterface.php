<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\DeletionConfirmation;

interface DeletionConfirmationRequestInterface
{
    public function getToken(): string;
}
