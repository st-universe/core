<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

interface ShowRegistrationRequestInterface
{
    public function getToken(): string;
}
