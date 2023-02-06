<?php

namespace Stu\Module\Index\Action\Register;

interface RegisterRequestInterface
{
    public function getLoginName(): string;

    public function getEmailAddress(): string;

    public function getMobileNumber(): string;

    public function getFactionId(): int;

    public function getToken(): string;
}
