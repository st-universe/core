<?php

declare(strict_types=1);

namespace Stu;

use Stu\Config\Init;
use Stu\Config\StuContainer;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Lib\Component\ComponentLoaderInterface;

class StuMocks
{
    private static ?StuMocks $INSTANCE = null;

    private StuContainer $dic;

    private function __construct()
    {
        $this->dic = Init::getContainer();
    }

    public static function get(): StuMocks
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new StuMocks();
        }

        return self::$INSTANCE;
    }

    public function mockService(string $id, mixed $serviceMock): StuMocks
    {
        $this->dic->setAdditionalService($id, $serviceMock);

        return $this;
    }

    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): StuMocks
    {
        $this->dic
            ->get(ComponentLoaderInterface::class)
            ->registerStubbedComponent($componentEnum);

        return $this;
    }

    public function reset(): void
    {
        $this->dic->clearAdditionalServices();
        $this->dic
            ->get(ComponentLoaderInterface::class)
            ->resetStubbedComponents();
    }
}
