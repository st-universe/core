<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Noodlehaus\ConfigInterface;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Index\News\NewsFactoryInterface;
use Stu\Component\Index\News\NewsItemInterface;
use Stu\Component\Player\UserCssEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class PlayerSettingsProvider implements ViewComponentProviderInterface
{
    private ConfigInterface $config;

    private NewsRepositoryInterface $newsRepository;

    private NewsFactoryInterface $newsFactory;

    public function __construct(
        ConfigInterface $config,
        NewsRepositoryInterface $newsRepository,
        NewsFactoryInterface $newsFactory
    ) {
        $this->config = $config;
        $this->newsRepository = $newsRepository;
        $this->newsFactory = $newsFactory;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );

        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
        $game->setTemplateVar('VIEWS', ModuleViewEnum::cases());
        $game->setTemplateVar('RPG_BEHAVIOR_VALUES', UserRpgBehaviorEnum::cases());
        $game->setTemplateVar('CSSSTYLE', $user->getCss());
        $game->setTemplateVar('CSS_VALUES', UserCssEnum::CSS_CLASS);
    }
}
