<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Entity\Relation;

final class UserRelationHistory
{
    public function __construct(private readonly EntryCreatorInterface $entryCreator) {}

    public function add(Relation $relation, int $sourceUserId, string $text): void
    {
        $targetUser = $relation->getRecipientUser() ?? $relation->getSourceUser();
        if ($targetUser === null) {
            return;
        }

        $this->entryCreator->createEntry(
            HistoryTypeEnum::ALLIANCE,
            $text,
            $sourceUserId,
            $targetUser->getId()
        );
    }
}
