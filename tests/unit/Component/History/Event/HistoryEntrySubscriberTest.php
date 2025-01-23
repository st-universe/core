<?php

declare(strict_types=1);

namespace Stu\Component\History\Event;

use Mockery\MockInterface;
use Override;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class HistoryEntrySubscriberTest extends StuTestCase
{
    /** @var MockInterface&EntryCreatorInterface */
    private MockInterface $entryCreator;

    private HistoryEntrySubscriber $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);

        $this->subject = new HistoryEntrySubscriber(
            $this->entryCreator
        );
    }

    public function testOnWarDeclarationCreatesEntry(): void
    {
        $event = $this->mock(WarDeclaredEvent::class);
        $alliance = $this->mock(AllianceInterface::class);
        $counterpart = $this->mock(AllianceInterface::class);
        $user = $this->mock(UserInterface::class);

        $allianceName = 'some-name';
        $counterpartName = 'some-other-name';
        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceName);

        $counterpart->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($counterpartName);

        $event->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $event->shouldReceive('getCounterpart')
            ->withNoArgs()
            ->twice()
            ->andReturn($counterpart);
        $event->shouldReceive('getResponsibleUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->entryCreator->shouldReceive('addEntry')
            ->with(
                sprintf(
                    'Die Allianz %s hat der Allianz %s den Krieg erklärt',
                    $allianceName,
                    $counterpartName
                ),
                $userId,
                $counterpart
            )
            ->once();

        $this->subject->onWarDeclaration($event);
    }
}
