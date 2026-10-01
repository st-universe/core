<?php

declare(strict_types=1);

use Noodlehaus\ConfigInterface;
use Stu\Config\Init;
use Stu\Extension\ExtensionRegistry;

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $container = Init::getContainer();
    $registry = $container->get(ExtensionRegistry::class);
    $id = $argv[1] ?? '--list';
    if ($id === '--list') {
        $services = [];
        foreach ($registry->all() as $name => $extension) {
            if (isset($extension['realtime']['entry'])) {
                $services[] = ['id' => $name, 'entry' => $extension['directory'] . '/' . $extension['realtime']['entry']];
            }
        }
        echo json_encode($services, JSON_THROW_ON_ERROR);
    } else {
        $extension = $registry->get($id);
        echo json_encode([
            'coreRoot' => dirname(__DIR__),
            'settings' => $extension['settings'],
            'db' => $container->get(ConfigInterface::class)->get('db')
        ], JSON_THROW_ON_ERROR);
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
