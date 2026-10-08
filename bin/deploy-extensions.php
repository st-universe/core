<?php

declare(strict_types=1);

use Stu\Config\ConfigFileSetup;
use Stu\Config\ConfigStageEnum;
use Stu\Extension\ExtensionDeployer;
use Stu\Extension\ExtensionInstaller;
use Stu\Extension\ExtensionRestarter;

require_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    ConfigFileSetup::initConfigStage(ConfigStageEnum::PRODUCTION);
    $extensions = ConfigFileSetup::load()->get('extensions', []);
    $installer = new ExtensionInstaller(dirname(__DIR__));
    $deployer = new ExtensionDeployer($installer);
    $restarter = new ExtensionRestarter(dirname(__DIR__));
    if (in_array('--restart', $argv, true)) {
        $result = $restarter->restart($extensions) + ['changed' => false];
    } else {
        $result = $deployer->deploy($extensions, in_array('--force', $argv, true));
    }
    foreach ($result['messages'] as $message) {
        fwrite(STDERR, $message . PHP_EOL);
    }
    exit(($result['failed'] ?? false) ? 1 : ($result['changed'] ? 10 : 0));
} catch (Throwable $error) {
    fwrite(STDERR, '[extensions] deployment failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
