<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use Crell\Tukio\Dispatcher;
use Crell\Tukio\OrderedListenerProvider;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Alliance\Event\Listener\DiplomaticRelationProposalCreationSubscriber;
use Stu\Component\History\Event\HistoryEntrySubscriber;

return [
    EventDispatcherInterface::class => function (ContainerInterface $c): EventDispatcherInterface {
        $provider = new OrderedListenerProvider($c);

        $provider->addSubscriber(DiplomaticRelationProposalCreationSubscriber::class, DiplomaticRelationProposalCreationSubscriber::class);
        $provider->addSubscriber(HistoryEntrySubscriber::class, HistoryEntrySubscriber::class);

        return new Dispatcher($provider);
    },
];
