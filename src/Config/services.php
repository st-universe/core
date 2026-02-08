<?php

declare(strict_types=1);

namespace Stu\Config;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use JBBCode\Parser;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Cache\CacheProvider;
use Stu\Component\Cache\CacheProviderInterface;
use Stu\Component\Logging\Sql\SqlLogger;
use Stu\Lib\ParserWithImage;
use Stu\Lib\ParserWithImageInterface;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Lib\StuBbCodeWithImageDefinitionSet;
use Stu\Module\Config\Model\SettingsCache;
use Stu\Module\Config\Model\SettingsCacheInterface;
use Stu\Module\Config\Model\SettingsFactory;
use Stu\Module\Config\Model\SettingsFactoryInterface;
use Stu\Module\Config\StuConfig;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ViewExecution;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Ubench;

use function DI\autowire;

return [
    ErrorHandler::class => autowire(),
    ConfigInterface::class => function (): ConfigInterface {
        $path = __DIR__ . '/../../config/';
        return new Config(
            array_map(fn (string $file): string => sprintf($file, $path), ConfigFileSetup::getConfigFileSetup())
        );
    },
    SettingsFactoryInterface::class => autowire(SettingsFactory::class),
    SettingsCacheInterface::class => autowire(SettingsCache::class),
    StuConfigInterface::class => autowire(StuConfig::class),
    CacheProviderInterface::class => autowire(CacheProvider::class),
    CacheItemPoolInterface::class => function (ContainerInterface $c): CacheItemPoolInterface {
        $stuConfig = $c->get(StuConfigInterface::class);

        if ($stuConfig->getCacheSettings()->useRedis()) {
            $cacheProvider = $c->get(CacheProviderInterface::class);

            return $cacheProvider->getRedisCachePool();
        } else {
            return new ArrayCachePool();
        }
    },
    SqlLogger::class => function (): SqlLogger {
        return new SqlLogger(
            StuLogger::getLogger(LogTypeEnum::DBAL)
        );
    },
    GameControllerInterface::class => autowire(GameController::class)
        ->constructorParameter('callbackExecution', autowire(CallbackExecution::class))
        ->constructorParameter('viewExecution', autowire(ViewExecution::class)),
    Parser::class => function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeDefinitionSet());
        return $parser;
    },
    ParserWithImageInterface::class => function (): ParserWithImage {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeWithImageDefinitionSet());
        return new ParserWithImage($parser);
    },
    JsonMapperInterface::class => fn (): JsonMapperInterface => new JsonMapperFactory()->bestFit(),
    Ubench::class => function (): Ubench {
        $bench = new Ubench();
        $bench->start();

        return $bench;
    },
    PasswordGeneratorInterface::class => function (): PasswordGeneratorInterface {
        $generator = new ComputerPasswordGenerator();

        $generator
            ->setOptionValue(ComputerPasswordGenerator::OPTION_UPPER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LOWER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_NUMBERS, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_SYMBOLS, false)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LENGTH, 10);

        return $generator;
    },
];
