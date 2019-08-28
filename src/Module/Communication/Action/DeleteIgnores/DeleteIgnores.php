<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteIgnores;

use Ignorelist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteIgnores implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_IGNORES';

    private $deleteIgnoresRequest;

    public function __construct(
        DeleteIgnoresRequestInterface $deleteIgnoresRequest
    ) {
        $this->deleteIgnoresRequest = $deleteIgnoresRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deleteIgnoresRequest->getIgnoreIds() as $key => $val) {
            $contact = Ignorelist::getById($val);
            if (!$contact || !$contact->isOwnIgnore()) {
                continue;
            }
            $contact->deleteFromDatabase();
        }
        $game->addInformation(_('Die Einträge wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
