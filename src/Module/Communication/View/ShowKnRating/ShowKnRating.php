<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnRating;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;

final class ShowKnRating implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_RATING';

    public function __construct(private KnFactoryInterface $knFactory)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/communication/knRating.twig');

        $post = $game->getViewContext(ViewContextTypeEnum::KN_POST);

        if ($post === null) {
            return;
        }

        $game->setTemplateVar(
            'STATUS_BAR',
            $this->knFactory
                ->createKnItem($post, $game->getUser())
                ->getRatingBar()
        );
    }
}
