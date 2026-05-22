<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use request;
use Stu\Orm\Entity\RegistrationReferralCode;
use Stu\Orm\Repository\RegistrationReferralCodeRepositoryInterface;

final class RegistrationReferralTracker implements RegistrationReferralTrackerInterface
{
    private const string REQUEST_PARAMETER = 'ref';
    private const string SESSION_KEY = 'stu_registration_ref';
    private const string COOKIE_NAME = 'stu_registration_ref';
    private const int COOKIE_TTL = 2_592_000;

    public function __construct(
        private RegistrationReferralCodeRepositoryInterface $registrationReferralCodeRepository
    ) {}

    #[\Override]
    public function captureFromRequest(): ?string
    {
        if (!array_key_exists(self::REQUEST_PARAMETER, request::getvars())) {
            return null;
        }

        $redirectTarget = $this->getRedirectTarget();
        $referralCode = $this->getValidReferralCode(request::getvars()[self::REQUEST_PARAMETER]);

        if ($referralCode !== null) {
            $this->registrationReferralCodeRepository->incrementHitCount($referralCode);
            $this->storeReferralCode($referralCode->getCode());
        }

        return $redirectTarget;
    }

    #[\Override]
    public function getStoredReferralCode(): ?string
    {
        $storedCode = $_SESSION[self::SESSION_KEY] ?? $_COOKIE[self::COOKIE_NAME] ?? null;
        if (!is_string($storedCode)) {
            return null;
        }

        return $this->getValidReferralCode($storedCode)?->getCode();
    }

    #[\Override]
    public function clearStoredReferralCode(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_COOKIE[self::COOKIE_NAME]);

        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $this->isSecureRequest(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    #[\Override]
    public function prependStoredReferralCode(?string $referer): ?string
    {
        $referralCode = $this->getStoredReferralCode();
        $referer = trim((string) $referer);

        if ($referralCode === null) {
            return $referer === '' ? null : $referer;
        }

        return $referer === '' ? $referralCode : sprintf('%s %s', $referralCode, $referer);
    }

    private function getValidReferralCode(mixed $code): ?RegistrationReferralCode
    {
        if (!is_scalar($code)) {
            return null;
        }

        $normalizedCode = $this->normalizeCode((string) $code);
        if ($normalizedCode === null) {
            return null;
        }

        return $this->registrationReferralCodeRepository->getActiveByCode($normalizedCode);
    }

    private function normalizeCode(string $code): ?string
    {
        $code = mb_strtolower(trim($code));

        return preg_match('/^[a-z0-9]{1,30}$/', $code) === 1 ? $code : null;
    }

    private function storeReferralCode(string $code): void
    {
        $_SESSION[self::SESSION_KEY] = $code;
        $_COOKIE[self::COOKIE_NAME] = $code;

        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, $code, [
                'expires' => time() + self::COOKIE_TTL,
                'path' => '/',
                'secure' => $this->isSecureRequest(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    private function getRedirectTarget(): string
    {
        $queryParams = request::getvars();
        unset($queryParams[self::REQUEST_PARAMETER]);

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $path = str_replace(["\r", "\n"], '', $path);
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = '/';
        }

        $query = http_build_query($queryParams);

        return $query === '' ? $path : sprintf('%s?%s', $path, $query);
    }

    private function isSecureRequest(): bool
    {
        return (
            isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && $_SERVER['HTTPS'] !== 'off'
        ) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
