<?php

namespace Stu\Module\Index\Action\DeletionConfirmation;

interface DeletionConfirmationRequestInterface
{
    public function getToken(): string;
}
