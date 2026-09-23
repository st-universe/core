<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\View;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\ControllerContext;
use Stu\Module\Control\ViewContextTypeEnum;

interface ViewControllerContext extends ControllerContext {

    public function getViewContext(ViewContextTypeEnum $type): mixed;
    
    public function setView(ModuleEnum|string $view): void;

    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void;

    public function setViewTemplate(string $viewTemplate): void;

    public function setTemplateFile(string $tpl): void;

    public function setMacroInAjaxWindow(string $macro): void;

    public function showMacro(string $macro): void;

    /**
     * Sets all navigation items at once
     *
     * @param array<array{url: string, title: string}> $navigationItems
     */
    public function setNavigation(
        array $navigationItems
    ): ViewControllerContext;

    public function appendNavigationPart(string $url, string $title): void;

    public function setPageTitle(string $title): void;

    public function getInfo(): InformationWrapper;

    public function getSessionString(): string;

    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void;

}
