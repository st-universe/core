<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\News;

use Mockery\MockInterface;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;
use Stu\StuApiV1TestCase;

class GetNewsTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|NewsRepositoryInterface
     */
    private $newsRepository;

    public function setUp(): void
    {
        $this->newsRepository = $this->mock(NewsRepositoryInterface::class);

        $this->setUpApiHandler(
            new GetNews(
                $this->newsRepository
            )
        );
    }

    public function testActionReturnsListOfNews(): void
    {
        $subject = 'some-subject';
        $text = 'some-text';
        $date = 123456;
        $links = ['http://heise.de'];

        $newsEntry = $this->mock(NewsInterface::class);

        $newsEntry->shouldReceive('getSubject')
            ->withNoArgs()
            ->once()
            ->andReturn($subject);
        $newsEntry->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn($text);
        $newsEntry->shouldReceive('getDate')
            ->withNoArgs()
            ->once()
            ->andReturn($date);
        $newsEntry->shouldReceive('getLinks')
            ->withNoArgs()
            ->once()
            ->andReturn($links);

        $this->newsRepository->shouldReceive('getRecent')
            ->with(5)
            ->once()
            ->andReturn([$newsEntry]);

        $this->response->shouldReceive('withData')
            ->with([
                [
                    'headline' => $subject,
                    'text' => $text,
                    'date' => $date,
                    'links' => $links
                ]
            ])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
