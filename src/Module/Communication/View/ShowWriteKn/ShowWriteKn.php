<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteKn;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionSet;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

/**
 * Displays the view for new kn postings
 */
final class ShowWriteKn implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'WRITE_KN';

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository, private CodeDefinitionSet $codeDefinitionSet)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setViewTemplate('html/communication/writeKn.twig');
        $game->appendNavigationPart('comm.php', 'KommNet');
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', self::VIEW_IDENTIFIER),
            'Beitrag schreiben'
        );
        $game->setPageTitle('Beitrag schreiben');

        $game->setTemplateVar(
            'ACTIVE_RPG_PLOTS',
            $this->rpgPlotRepository->getActiveByUser($game->getUser()->getId())
        );
        $game->setTemplateVar(
            'ALLOWED_BBCODE_CHARACTERS',
            array_map(
                fn (CodeDefinition $definition): string => $definition->getTagName(),
                $this->codeDefinitionSet->getCodeDefinitions()
            )
        );
    }
}
