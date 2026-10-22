<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

final class TrustedTemplateLoader extends FilesystemLoader
{
    #[\Override]
    public function exists(string $name): bool
    {
        return $this->findTemplate($name, false) !== null;
    }

    #[\Override]
    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        $name = str_replace('\\', '/', $name);
        $namespace = self::MAIN_NAMESPACE;
        if (str_starts_with($name, '@') && ($separator = strpos($name, '/')) !== false) {
            $namespace = substr($name, 1, $separator - 1);
            if ($namespace === self::MAIN_NAMESPACE) {
                $name = substr($name, $separator + 1);
            }
        }
        if ($namespace === self::MAIN_NAMESPACE && str_starts_with($name, 'html/')) {
            $name = substr($name, strlen('html/'));
        }

        $path = parent::findTemplate($name, $throw);
        if ($path === null) {
            return null;
        }
        foreach ($this->getPaths($namespace) as $directory) {
            $root = realpath($directory);
            if ($root !== false && str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
                return $path;
            }
        }
        if ($throw) {
            throw new LoaderError('Template is outside its configured template directory');
        }
        return null;
    }
}
