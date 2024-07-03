<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class RegisterRequest implements RegisterRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getLoginName(): string
    {
        return $this->queryParameter('loginname')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getEmailAddress(): string
    {
        return $this->queryParameter('email')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getMobileNumber(): string
    {
        return trim($this->queryParameter('mobile')->string()->defaultsToIfEmpty(''));
    }

    #[Override]
    public function getCountryCode(): string
    {
        return $this->queryParameter('countrycode')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->queryParameter('factionid')->int()->required();
    }

    #[Override]
    public function getToken(): string
    {
        $token = $this->queryParameter('token')->string()->defaultsToIfEmpty('');

        return preg_replace('/[\W_]+/', '', $token);
    }
}
