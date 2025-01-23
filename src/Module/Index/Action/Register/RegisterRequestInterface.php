<?php

namespace Stu\Module\Index\Action\Register;

interface RegisterRequestInterface
{
    public function getLoginName(): string;

    public function getEmailAddress(): string;

    public function getMobileNumber(): string;

    public function getCountryCode(): string;

    public function getFactionId(): int;

    public function getToken(): string;

    public function getReferer(): ?string;
}
