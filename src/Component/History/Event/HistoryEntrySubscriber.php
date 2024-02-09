<?php

declare(strict_types=1);

namespace Stu\Component\History\Event;

use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Module\History\Lib\EntryCreatorInterface;

/**
 * Listens for history related events and creates corresponding entries
 */
final class HistoryEntrySubscriber
{
    private EntryCreatorInterface $entryCreator;

    public function __construct(
        EntryCreatorInterface $entryCreator
    ) {
        $this->entryCreator = $entryCreator;
    }

    /**
     * Creates history entries for war declarations
     */
    public function onWarDeclaration(
        WarDeclaredEvent $event
    ): void {
        $this->entryCreator->addAllianceEntry(
            sprintf(
                'Die Allianz %s hat der Allianz %s den Krieg erklärt',
                $event->getAlliance()->getName(),
                $event->getCounterpart()->getName()
            ),
            $event->getResponsibleUser()->getId(),
            $event->getCounterpart()->getFounder()->getId()
        );
    }
}