<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Override;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnFactory implements KnFactoryInterface
{
    public function __construct(
        private KnBbCodeParser $bbCodeParser,
        private KnCommentRepositoryInterface $knCommentRepository,
        private StatusBarFactoryInterface $statusBarFactory
    ) {}

    #[Override]
    public function createKnItem(
        KnPost $knPost,
        User $currentUser
    ): KnItemInterface {
        return new KnItem(
            $this->bbCodeParser,
            $this->knCommentRepository,
            $this->statusBarFactory,
            $knPost,
            $currentUser
        );
    }
}
