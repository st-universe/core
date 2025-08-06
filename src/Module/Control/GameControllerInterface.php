<?php

namespace Stu\Module\Control;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;

interface GameControllerInterface
{
    public function setView(ModuleEnum|string $view): void;

    public function getViewContext(ViewContextTypeEnum $type): mixed;

    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void;

    public function setViewTemplate(string $viewTemplate): void;

    public function setTemplateFile(string $tpl): void;

    public function setMacroInAjaxWindow(string $macro): void;

    public function showMacro(string $macro): void;

    public function getGameData(): GameData;

    public function getInfo(): InformationWrapper;

    public function setTemplateVar(string $key, mixed $variable): void;

    public function getUser(): User;

    public function hasUser(): bool;

    public function isNpc(): bool;

    /**
     * Sets all navigation items at once
     *
     * @param array<array{url: string, title: string}> $navigationItems
     */
    public function setNavigation(
        array $navigationItems
    ): GameControllerInterface;

    public function appendNavigationPart(string $url, string $title): void;

    public function setPageTitle(string $title): void;

    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void;

    public function getCurrentRound(): GameTurn;

    public function getSessionString(): string;

    public function sessionAndAdminCheck(): void;

    public function getGameRequest(): GameRequest;

    public function main(
        ModuleEnum $view,
        bool $session_check = true,
        bool $admin_check = false,
        bool $npc_check = false
    ): void;

    public function isAdmin(): bool;

    public function resetGameData(): void;

    /**
     * @return array{executionTime: float|string, memoryPeakUsage: float|string}
     */
    public function getBenchmarkResult(): array;
}
