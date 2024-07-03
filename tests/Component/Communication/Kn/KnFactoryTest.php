<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;
use Mockery\MockInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\StuTestCase;

class KnFactoryTest extends StuTestCase
{
    /**
     * @var null|MockInterface|Parser
     */
    private $bbcodeParser;

    /**
     * @var null|MockInterface|KnCommentRepositoryInterface
     */
    private $knCommentRepository;

    /**
     * @var null|KnFactory
     */
    private KnFactoryInterface $factory;

    public function setUp(): void
    {
        $this->bbcodeParser = $this->mock(Parser::class);
        $this->knCommentRepository = $this->mock(KnCommentRepositoryInterface::class);

        $this->factory = new KnFactory(
            $this->bbcodeParser,
            $this->knCommentRepository
        );
    }

    public function testCreateKnItemReturnsValue(): void
    {
        $knPost = $this->mock(KnPostInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->assertInstanceOf(
            KnItem::class,
            $this->factory->createKnItem(
                $knPost,
                $user
            )
        );
    }
}
