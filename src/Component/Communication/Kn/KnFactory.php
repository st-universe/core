<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnFactory implements KnFactoryInterface {

    private Parser $bbcodeParser;

    private KnCommentRepositoryInterface $knCommentRepository;

    public function __construct(
        Parser $bbcodeParser,
        KnCommentRepositoryInterface $knCommentRepository
    ) {
        $this->bbcodeParser = $bbcodeParser;
        $this->knCommentRepository = $knCommentRepository;
    }

    public function createKnItem(
        KnPostInterface $knPost,
        UserInterface $currentUser
    ): KnItemInterface {
       return new KnItem(
           $this->bbcodeParser,
           $this->knCommentRepository,
           $knPost,
           $currentUser
       );
    }
}
