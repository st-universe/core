<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacters;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;

final class ShowKnCharacters implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_CHARACTERS';

    public function __construct(private ShowKnCharactersRequestInterface $showKnCharactersRequest, private UserCharacterRepositoryInterface $userCharactersRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $characterId = $this->showKnCharactersRequest->getCharacterId();

        $character = $this->userCharactersRepository->find($characterId);

        if ($character === null) {
            return;
        }

        $game->setPageTitle(sprintf(_('Details zu Charakter %s'), $character->getName()));
        $game->setMacroInAjaxWindow('html/communication/knCharacters.twig');
        $game->setTemplateVar('CHARACTER', $character);
    }
}
