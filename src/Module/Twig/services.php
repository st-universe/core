<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Psr\Container\ContainerInterface;
use Stu\Extension\ExtensionRegistry;
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
        $extensions = $c->get(ExtensionRegistry::class);
        foreach ($extensions->all() as $extension) {
            foreach ($extension['templates'] ?? [] as $namespace => $path) {
                $loader->addPath($extension['directory'] . '/' . $path, $namespace);
            }
        }

        $environment = new Environment($loader, [
            'cache' => $cache,
        ]);
        $environment->addGlobal('EXTENSIONS', $extensions);
        return $environment;
    },
    TwigPageInterface::class => autowire(TwigPage::class),
    TwigHelper::class => autowire(TwigHelper::class)
];
