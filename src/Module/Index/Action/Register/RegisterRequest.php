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
        return $this->parameter('loginname')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getEmailAddress(): string
    {
        return $this->parameter('email')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getMobileNumber(): string
    {
        return trim($this->parameter('mobile')->string()->defaultsToIfEmpty(''));
    }

    #[Override]
    public function getCountryCode(): string
    {
        return $this->parameter('countrycode')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->parameter('factionid')->int()->required();
    }

    #[Override]
    public function getToken(): string
    {
        $token = $this->parameter('token')->string()->defaultsToIfEmpty('');

        return preg_replace('/[\W_]+/', '', $token);
    }

    #[Override]
    public function getReferer(): ?string
    {
        return $this->parameter('referer')->string()->defaultsToIfEmpty(null);
    }
}
