<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\User;

final class AllianceForumPermission
{
    public function __construct(private AllianceJobManagerInterface $allianceJobManager) {}

    public function assertCanEditPost(User $user, AllianceBoardPost $post): void
    {
        if ($user->getAlliance()?->getId() !== $post->getBoard()->getAlliance()->getId()
            || $user->getId() !== $post->getUser()->getId()) {
            throw new AccessViolationException();
        }
    }

    public function assertCanManageForum(User $user, Alliance $alliance): void
    {
        if ($user->getAlliance()?->getId() !== $alliance->getId()
            || !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)) {
            throw new AccessViolationException();
        }
    }
}
