<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

interface RegistrationReferralTrackerInterface
{
    public function captureFromRequest(): ?string;

    public function getStoredReferralCode(): ?string;

    public function clearStoredReferralCode(): void;

    public function prependStoredReferralCode(?string $referer): ?string;
}
