<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\RumpCreator;

use Stu\Module\Admin\Lib\RumpCreatorData;
use Stu\Module\Admin\Lib\RumpCreatorDataProvider;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowRumpCreator implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RUMP_CREATOR';

    public function __construct(private readonly RumpCreatorDataProvider $dataProvider) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setTemplateFile('html/admin/rumpCreator.twig');
        $game->appendNavigationPart('/admin/?SHOW_RUMP_CREATOR=1', _('Rump Creator'));
        $game->setPageTitle(_('Admin: Rump Creator'));
        $game->setTemplateVar('RUMPS', $this->dataProvider->getRumps()->all());
        $game->setTemplateVar('RUMP_TEMPLATES', $this->dataProvider->getTemplates());

        foreach ($this->dataProvider->getOptions()->all() as $key => $value) {
            $value = $value instanceof RumpCreatorData ? $value->all() : $value;
            $game->setTemplateVar($key, $value);
        }
    }
}
