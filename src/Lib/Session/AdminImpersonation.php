<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Exception\SessionInvalidException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AdminImpersonation implements AdminImpersonationInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly StuConfigInterface $stuConfig
    ) {}

    #[\Override]
    public function isActive(): bool
    {
        return $this->getTargetId() !== null;
    }

    #[\Override]
    public function getAdminId(): ?int
    {
        return $this->isActive() ? (int) ($_SESSION['uid'] ?? 0) : null;
    }

    #[\Override]
    public function getTargetId(): ?int
    {
        $targetId = $_SESSION['impersonated_user_id'] ?? null;

        return is_int($targetId) && $targetId > 0 ? $targetId : null;
    }

    #[\Override]
    public function resolveUser(User $admin, bool $adminContext): User
    {
        $targetId = $this->getTargetId();
        if ($targetId === null) {
            return $admin;
        }

        $this->checkAdmin($admin);
        if ($adminContext) {
            return $admin;
        }

        $target = $this->userRepository->find($targetId);
        if ($target === null) {
            throw new SessionInvalidException('Spieleraccount nicht gefunden');
        }
        $this->checkTarget($admin, $target);

        return $target;
    }

    #[\Override]
    public function start(User $admin, User $target): void
    {
        $this->checkAdmin($admin);
        $this->checkTarget($admin, $target);
        $_SESSION['impersonated_user_id'] = $target->getId();
    }

    #[\Override]
    public function stop(): void
    {
        unset($_SESSION['impersonated_user_id']);
    }

    private function checkAdmin(User $admin): void
    {
        if (
            ($_SESSION['login'] ?? null) != 1
            || (int) ($_SESSION['uid'] ?? 0) !== $admin->getId()
            || !in_array($admin->getId(), $this->stuConfig->getGameSettings()->getAdminIds(), true)
            || $admin->isLocked()
            || $admin->getRegistration()->getDeletionMark() === UserConstants::DELETION_CONFIRMED
        ) {
            throw new SessionInvalidException('Admin-Zugriff nicht erlaubt');
        }
    }

    private function checkTarget(User $admin, User $target): void
    {
        if (
            $target->getId() === $admin->getId()
            || $target->getId() < UserConstants::USER_FIRST_NPC
            || in_array($target->getId(), $this->stuConfig->getGameSettings()->getAdminIds(), true)
            || $target->isLocked()
            || $target->getState() === UserStateEnum::NEW
            || $target->getRegistration()->getDeletionMark() === UserConstants::DELETION_CONFIRMED
        ) {
            throw new SessionInvalidException('Admin-Zugriff auf diesen Spieleraccount nicht möglich');
        }
    }
}
