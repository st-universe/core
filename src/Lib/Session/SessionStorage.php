<?php

namespace Stu\Lib\Session;

use Override;
use Stu\Exception\SessionInvalidException;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SessionStorage implements SessionStorageInterface
{
    /** @var array<array<mixed>> */
    private array $sessionDataPerUser = [];

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SessionInterface $session
    ) {}

    /**
     * @api
     */
    #[Override]
    public function storeSessionData(string|int $key, mixed $value, bool $isSingleValue = false): void
    {
        $stored = false;
        $user = $this->getUserMandatory();

        $data = $this->getSessionDataUnserialized($user);
        if (!array_key_exists($key, $data)) {
            if ($isSingleValue) {
                $data[$key] = $value;
                $stored = true;
            } else {
                $data[$key] = [];
            }
        }
        if (!$isSingleValue && !array_key_exists($value, $data[$key])) {
            $data[$key][$value] = 1;
            $stored = true;
        }

        if ($stored) {
            unset($this->sessionDataPerUser[$user->getId()]);
            $user->setSessionData(serialize($data));
            $this->userRepository->save($user);
        }
    }

    /** @return array<mixed> */
    private function getSessionDataUnserialized(UserInterface $user): array
    {
        if (!array_key_exists($user->getId(), $this->sessionDataPerUser)) {
            $sessiondataUnserialized = unserialize($user->getSessionData());
            if (!is_array($sessiondataUnserialized)) {
                $sessiondataUnserialized = [];
            }
            $this->sessionDataPerUser[$user->getId()] = $sessiondataUnserialized;
        }

        return $this->sessionDataPerUser[$user->getId()];
    }

    /**
     * @api
     */
    #[Override]
    public function deleteSessionData(string $key, mixed $value = null): void
    {
        $user = $this->getUserMandatory();

        $data = $this->getSessionDataUnserialized($user);
        if (!array_key_exists($key, $data)) {
            return;
        }
        if ($value === null) {
            unset($data[$key]);
        } else {
            if (!array_key_exists($value, $data[$key])) {
                return;
            }
            unset($data[$key][$value]);
        }
        $user->setSessionData(serialize($data));
        $this->userRepository->save($user);
    }

    /**
     * @api
     */
    #[Override]
    public function hasSessionValue(string $key, mixed $value): bool
    {
        $data = $this->getSessionDataUnserialized($this->getUserMandatory());
        if (!array_key_exists($key, $data)) {
            return false;
        }
        return array_key_exists($value, $data[$key]);
    }

    /**
     * @api
     */
    #[Override]
    public function getSessionValue(string $key): mixed
    {
        $data = $this->getSessionDataUnserialized($this->getUserMandatory());
        if (!array_key_exists($key, $data)) {
            return false;
        }
        return $data[$key];
    }

    private function getUserMandatory(): UserInterface
    {
        $user = $this->session->getUser();
        if ($user === null) {
            throw new SessionInvalidException("No user logged in");
        }

        return $user;
    }
}
