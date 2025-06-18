<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonyList implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    public function __construct(private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $state = $user->getState();

        if ($state !== UserEnum::USER_STATE_UNCOLONIZED) {
            throw new AccessViolation(sprintf(
                _('User is not uncolonized, but tried to enter first-colony-list. Fool: %d, State: %d'),
                $user->getId(),
                $state
            ));
        }
        $game->setViewTemplate("html/maindesk/colonylist.twig");
        $game->setPageTitle("Kolonie gründen");
        $game->appendNavigationPart(
            sprintf(
                '?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Kolonie gründen')
        );

        $game->setTemplateVar(
            'FREE_PLANET_LIST',
            $this->colonyRepository->getStartingByFaction($user->getFactionId())
        );
    }
    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::MAINDESK, self::VIEW_IDENTIFIER);
    }
}
