<?php

declare(strict_types=1);

namespace Stu;

use request;
use Stu\Component\Database\AchievementManager;
use Stu\Config\Init;
use Stu\Config\StuContainer;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Lib\Component\ComponentLoaderInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Module\Control\JavascriptExecution;
use Stu\Module\Control\SemaphoreUtil;
use Stu\Module\Logging\StuLogger;

class StuMocks
{
    private static ?StuMocks $instance = null;

    public static function get(): StuMocks
    {
        if (self::$instance === null) {
            self::$instance = new StuMocks();
        }

        return self::$instance;
    }

    public function mockService(string $id, mixed $serviceMock): StuMocks
    {
        $this->getStuContainer()->setAdditionalService($id, $serviceMock);

        return $this;
    }

    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): StuMocks
    {
        $this->getStuContainer()
            ->get(ComponentLoaderInterface::class)
            ->registerStubbedComponent($componentEnum);

        return $this;
    }

    public function reset(): void
    {
        request::setMockVars(null);
        PanelLayerCreation::$skippedLayers = [];
        $this->getStuContainer()->clearAdditionalServices();
        $this->getStuContainer()
            ->get(ComponentLoaderInterface::class)
            ->resetStubbedComponents();
        StuLogger::setMock(null);
        AchievementManager::reset();
        JavascriptExecution::reset();
        SemaphoreUtil::reset();
    }

    private function getStuContainer(): StuContainer
    {
        return Init::getContainer();
    }
}
