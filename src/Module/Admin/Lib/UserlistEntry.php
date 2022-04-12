<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Component\Player\PlayerTagTypeEnum;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserTagInterface;
use Stu\Orm\Repository\UserTagRepositoryInterface;

final class UserlistEntry
{
    private UserTagRepositoryInterface $userTagRepository;

    private UserInterface $user;

    public function __construct(
        UserTagRepositoryInterface $userTagRepository,
        UserInterface $user
    ) {
        $this->userTagRepository = $userTagRepository;
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getUserStateDescription(): string
    {
        return $this->user->getUserStateDescription();
    }

    public function getUserStateColor(): string
    {
        $user = $this->user;
        if ($user->isLocked()) {
            return _("red");
        }
        if ($user->getActive() === PlayerEnum::USER_ACTIVE) {
            return _("greenyellow");
        }
        return '#dddddd';
    }

    public function isUserActive(): bool
    {
        return $this->user->getActive() === PlayerEnum::USER_ACTIVE;
    }

    public function isUserLocked(): bool
    {
        return $this->user->isLocked();
    }

    public function getTags(): iterable
    {
        return array_map(
            function (UserTagInterface $tag): array {
                return [
                    'label' => PlayerTagTypeEnum::TYPE_TO_LABEL[$tag->getTagTypeId()],
                    'typeId' => $tag->getTagTypeId(),
                    'date' => $tag->getDate()->format('u')
                ];
            },
            $this->userTagRepository->getByUser($this->user)
        );
    }
}
