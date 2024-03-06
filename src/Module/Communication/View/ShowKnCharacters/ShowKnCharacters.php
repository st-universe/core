<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacters;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserCharactersRepositoryInterface;

final class ShowKnCharacters implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_KN_CHARACTERS';

    private ShowKnCharactersRequestInterface $showKnCharactersRequest;
    private UserCharactersRepositoryInterface $userCharactersRepository;

    public function __construct(
        ShowKnCharactersRequestInterface $showKnCharactersRequest,
        UserCharactersRepositoryInterface $userCharactersRepository
    ) {
        $this->showKnCharactersRequest = $showKnCharactersRequest;
        $this->userCharactersRepository = $userCharactersRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $characterId = $this->showKnCharactersRequest->getCharacterId();

        $character = $this->userCharactersRepository->find($characterId);

        if ($character === null) {
            return;
        }

        $game->setPageTitle(sprintf(_('Details zu Charakter %s'), $character->getName()));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/kncharacters');
        $game->setTemplateVar('CHARACTER', $character);
    }
}
