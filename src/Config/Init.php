<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Tal\TalHelper;
use Stu\Module\Twig\TwigHelper;

/**
 * Inits the application by calling the provided callable and injecting the DIC
 */
final class Init
{
    /**
     * @param callable(ContainerInterface): mixed $app
     */
    public static function run(callable $app): void
    {
        date_default_timezone_set('Europe/Berlin');

        // ordered alphabetically
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Admin/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Alliance/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Building/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Cli/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Crew/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Colony/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Communication/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Game/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/GrapViz/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/History/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Image/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Index/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Logging/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Player/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Ship/services.php');
        $builder->addDefinitions(__DIR__ . '/../Component/Station/services.php');
        $builder->addDefinitions(__DIR__ . '/../Lib/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Admin/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Alliance/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Award/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Building/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Colony/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Communication/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Control/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Crew/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Database/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/History/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Index/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Logging/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Maindesk/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Maintenance/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Message/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Notes/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/PlayerProfile/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/PlayerSetting/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Prestige/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Research/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Ship/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Starmap/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Station/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Tal/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Tick/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Trade/services.php');
        $builder->addDefinitions(__DIR__ . '/../Module/Twig/services.php');
        $builder->addDefinitions(__DIR__ . '/../Orm/Repository/services.php',);

        /** @var ContainerInterface $container */
        $container = $builder->build();

        $config = $container->get(StuConfigInterface::class);
        $container->get(ErrorHandler::class)->register();

        set_include_path(get_include_path() . PATH_SEPARATOR . $config->getGameSettings()->getWebroot());

        // PHPTAL
        TalHelper::register($container);

        // TWIG
        $twigHelper = $container->get(TwigHelper::class);
        $twigHelper->registerMethodsAndFilters();

        $app($container);
    }
}
