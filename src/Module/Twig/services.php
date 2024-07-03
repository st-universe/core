<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Psr\Container\ContainerInterface;
use Stu\Module\Config\StuConfigInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function DI\autowire;

return [
    Environment::class => function (ContainerInterface $c): Environment {
        $stuConfig = $c->get(StuConfigInterface::class);

        $templatePath = realpath(
            sprintf(
                '%s/../',
                $stuConfig->getGameSettings()->getWebroot()
            )
        );

        $cache = false;
        if (!$stuConfig->getDebugSettings()->isDebugMode()) {
            $cache = sprintf(
                '%s/stu/%s/twig',
                $stuConfig->getGameSettings()->getTempDir(),
                $stuConfig->getGameSettings()->getVersion()
            );
        }

        $loader = new FilesystemLoader($templatePath ?: []);

        return new Environment($loader, [
            'cache' => $cache,
        ]);
    },
    TwigPageInterface::class => autowire(TwigPage::class),
    TwigHelper::class => autowire(TwigHelper::class)
];
