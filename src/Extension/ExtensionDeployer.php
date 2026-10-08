<?php

declare(strict_types=1);

namespace Stu\Extension;

use Throwable;

final class ExtensionDeployer
{
    public function __construct(
        private ExtensionInstaller $installer
    ) {}

    public function deploy(array $extensions, bool $force = false): array
    {
        $result = ['changed' => false, 'messages' => []];
        foreach ($extensions as $id => $settings) {
            if (($settings['enabled'] ?? false) !== true || !isset($settings['deployment'])) {
                continue;
            }
            try {
                $changed = $this->installer->install($id, $settings, $force);
                $result['changed'] = $result['changed'] || $changed;
                if ($changed) {
                    $result['messages'][] = '[extensions] ' . $id . ' updated';
                }
            } catch (Throwable $error) {
                $result['messages'][] = '[extensions] ' . $id . ' skipped: ' . $error->getMessage();
            }
        }
        return $result;
    }
}
