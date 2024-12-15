<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Twig\TwigHelper;

/**
 * Inits the application by calling the provided callable and injecting the DIC
 */
final class Init
{
    private static ?StuContainer $CONTAINER = null;

    public static function getContainer(ConfigStageEnum $stage = ConfigStageEnum::PRODUCTION, bool $doReload = false): StuContainer
    {
        if (static::$CONTAINER === null || $doReload) {
            ConfigFileSetup::initConfigStage($stage);

            // ordered alphabetically
            $builder = new ContainerBuilder(StuContainer::class);
            $builder->addDefinitions(__DIR__ . '/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Admin/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Alliance/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Anomaly/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Building/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Cli/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Colony/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Communication/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Crew/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Game/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/GrapViz/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/History/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Image/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Index/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Logging/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Map/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Player/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Refactor/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Ship/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Spacecraft/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/Station/services.php');
            $builder->addDefinitions(__DIR__ . '/../Component/StarSystem/services.php');
            $builder->addDefinitions(__DIR__ . '/../Lib/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Admin/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Alliance/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Award/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Building/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Colony/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Commodity/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Communication/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Control/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Crew/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Database/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Game/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/History/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Index/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Logging/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Maindesk/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Maintenance/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Message/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Notes/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/NPC/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/PlayerProfile/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/PlayerSetting/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Prestige/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Research/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Ship/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Spacecraft/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Starmap/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Station/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Template/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Tick/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Trade/services.php');
            $builder->addDefinitions(__DIR__ . '/../Module/Twig/services.php');
            $builder->addDefinitions(__DIR__ . '/../Orm/Repository/services.php');

            static::$CONTAINER = $builder->build();
        }

        return static::$CONTAINER;
    }

    /**
     * @param callable(ContainerInterface): mixed $app
     */
    public static function run(callable $app, bool $registerErrorHandlers = true): void
    {
        date_default_timezone_set('Europe/Berlin');

        $container = self::getContainer();

        $config = $container->get(StuConfigInterface::class);

        $container->get(ErrorHandler::class)->register($registerErrorHandlers);

        set_include_path(get_include_path() . PATH_SEPARATOR . $config->getGameSettings()->getWebroot());

        // TWIG
        $twigHelper = $container->get(TwigHelper::class);
        $twigHelper->registerFiltersAndFunctions();
        $twigHelper->registerGlobalVariables();

        $app($container);
    }
}
