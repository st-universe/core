<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\Container;
use DI\Definition\ArrayDefinition;
use DI\Definition\Definition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Proxy\ProxyFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Container\ContainerInterface;

use function DI\get;

class StuContainer extends Container
{
    private MutableDefinitionSource $definitionSource;

    /** @var array<string, Definition> */
    private ?array $definitions = null;

    /** @var Collection<string, Collection<int|string, mixed>> */
    private Collection $services;

    /**
     * @param ContainerInterface $wrapperContainer If the container is wrapped by another container.
     */
    public function __construct(
        MutableDefinitionSource $definitions,
        ?ProxyFactory $proxyFactory = null,
        ?ContainerInterface $wrapperContainer = null
    ) {
        parent::__construct($definitions, $proxyFactory, $wrapperContainer);

        $this->definitionSource = $definitions;
        $this->services = new ArrayCollection();
    }

    /**
     * @template T
     * @param class-string<T> $interfaceName
     * 
     * @return Collection<int|string, T>
     */
    public function getDefinedImplementationsOf(string $interfaceName, bool $addDefinitionKey = false): Collection
    {
        $services = $this->services->get($interfaceName);
        if ($services === null) {
            $services = $this->getServices($interfaceName, $addDefinitionKey);
        }

        return $services;
    }

    /**
     * @template T
     * @param class-string<T> $interfaceName
     * 
     * @return Collection<int, T>
     */
    private function getServices(string $interfaceName, bool $addDefinitionKey): Collection
    {
        $services = new ArrayCollection();

        $definitions = $this->getDefinitions();

        foreach ($definitions as $definitionKey => $definition) {

            if ($definition instanceof ArrayDefinition) {

                foreach (
                    get($definitionKey)->resolve($this->delegateContainer)
                    as $arrayKey => $service
                ) {
                    $this->addDefinition(
                        $service,
                        $addDefinitionKey ? sprintf('%s-%s', $definitionKey, $arrayKey) : $arrayKey,
                        $services,
                        $interfaceName
                    );
                }
            } elseif (!$definition instanceof FactoryDefinition) {
                $this->addDefinition(
                    get($definitionKey)->resolve($this->delegateContainer),
                    $definitionKey,
                    $services,
                    $interfaceName
                );
            }
        }

        $this->services->set($interfaceName, $services);

        return $services;
    }

    /** @return array<string, Definition> */
    private function getDefinitions(): array
    {
        if ($this->definitions === null) {
            $this->definitions = $this->definitionSource->getDefinitions();
        }

        return $this->definitions;
    }

    /**
     * @template T
     * @param class-string<T> $interfaceName
     * @param T $service
     * @param Collection<int|string, T> $services
     */
    private function addDefinition(
        $service,
        int|string $key,
        Collection $services,
        string $interfaceName
    ): void {
        $classImplements = class_implements($service);
        if (!$classImplements) {
            return;
        }

        if (in_array($interfaceName, $classImplements)) {
            $services->set($key, $service);
        }
    }
}
