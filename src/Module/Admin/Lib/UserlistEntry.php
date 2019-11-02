<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Component\Player\PlayerTagTypeEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserTagInterface;
use Stu\Orm\Repository\UserTagRepositoryInterface;

final class UserlistEntry
{
    private $userTagRepository;

    private $user;

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
