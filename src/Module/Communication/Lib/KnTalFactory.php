<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use UserData;

final class KnTalFactory implements KnTalFactoryInterface
{
    private $knCommentRepository;

    public function __construct(
        KnCommentRepositoryInterface $knCommentRepository
    ) {
        $this->knCommentRepository = $knCommentRepository;
    }

    public function createKnPostTal(
        KnPostInterface $post,
        UserData $user
    ): KnPostTalInterface {
        return new KnPostTal(
            $this->knCommentRepository,
            $post,
            $user
        );
    }
}