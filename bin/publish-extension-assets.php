<?php

declare(strict_types=1);

use Stu\Config\Init;
use Stu\Extension\ExtensionRegistry;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__) . '/src/Public/static/extensions';
foreach (Init::getContainer()->get(ExtensionRegistry::class)->all() as $id => $extension) {
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $id)) {
        throw new RuntimeException('Invalid extension asset directory');
    }
    foreach ($extension['assets'] ?? [] as $name => $relativePath) {
        if (basename($name) !== $name || $name === '.' || $name === '..') {
            throw new RuntimeException('Invalid extension asset name');
        }
        $directory = $root . '/' . $id;
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create extension asset directory');
        }
        $source = realpath($extension['directory'] . '/' . $relativePath);
        if ($source === false || !str_starts_with($source, $extension['directory'] . '/')) {
            throw new RuntimeException('Invalid extension asset source');
        }
        if (!copy($source, $directory . '/' . $name)) {
            throw new RuntimeException('Cannot publish extension asset');
        }
        echo $id . '/' . $name . PHP_EOL;
    }
}
