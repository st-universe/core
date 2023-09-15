<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class RegisterRequest implements RegisterRequestInterface
{
    use CustomControllerHelperTrait;

    public function getLoginName(): string
    {
        return $this->queryParameter('loginname')->string()->defaultsToIfEmpty('');
    }

    public function getEmailAddress(): string
    {
        return $this->queryParameter('email')->string()->defaultsToIfEmpty('');
    }

    public function getMobileNumber(): string
    {
        return trim($this->queryParameter('mobile')->string()->defaultsToIfEmpty(''));
    }

    public function getCountryCode(): string
    {
        return $this->queryParameter('countrycode')->string()->defaultsToIfEmpty('');
    }

    public function getFactionId(): int
    {
        return $this->queryParameter('factionid')->int()->required();
    }

    public function getToken(): string
    {
        $token = $this->queryParameter('token')->string()->defaultsToIfEmpty('');

        return preg_replace('/[\W_]+/', '', $token);
    }
}
