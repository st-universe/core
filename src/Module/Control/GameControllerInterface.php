<?php

namespace Stu\Module\Control;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use SysvSemaphore;

interface GameControllerInterface extends InformationInterface
{
    public function setView(ModuleEnum|string $view): void;

    public function getViewContext(ViewContextTypeEnum $type): mixed;

    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void;

    public function getGameState(): int;

    public function setViewTemplate(string $viewTemplate): void;

    public function setTemplateFile(string $tpl): void;

    public function setMacroInAjaxWindow(string $macro): void;

    public function showMacro(string $macro): void;

    public function getMacro(): string;

    /** @param array<string> $info */
    public function addInformationMerge(array $info): void;

    public function addInformationWrapper(?InformationWrapper $informations, bool $isHead = false): void;

    /** @return array<string> */
    public function getInformation(): array;

    public function getTargetLink(): ?TargetLink;

    public function setTargetLink(TargetLink $targetLink): GameControllerInterface;

    public function setTemplateVar(string $key, mixed $variable);

    public function getUser(): UserInterface;

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

    /**
     * @return array<string, string>
     */
    public function getNavigation(): array;

    public function getPageTitle(): string;

    public function setPageTitle(string $title): void;

    /** @return array<string>|null */
    public function getExecuteJS(int $when): ?array;

    public function addExecuteJS(string $value, int $when = GameEnum::JS_EXECUTION_BEFORE_RENDER): void;

    public function redirectTo(string $href): void;

    public function getCurrentRound(): GameTurnInterface;

    public function checkDatabaseItem(?int $databaseEntryId): void;

    /** @return array<string> */
    public function getAchievements(): array;

    public function getSessionString(): string;

    public function sessionAndAdminCheck(): void;

    public function getGameRequest(): GameRequestInterface;

    public function getGameRequestId(): string;

    public function main(
        ModuleEnum $view,
        bool $session_check = true,
        bool $admin_check = false,
        bool $npc_check = false
    ): void;

    public function isAdmin(): bool;

    public function isSemaphoreAlreadyAcquired(int $key): bool;

    public function addSemaphore(int $key, SysvSemaphore $semaphore): void;

    public function triggerEvent(object $event): void;

    /** @return array{currentTurn: int, player: int, playeronline: int, gameState: int, gameStateTextual: string} */
    public function getGameStats(): array;

    public function getGameStateTextual(): string;

    public function resetGameData(): void;

    public function getBenchmarkResult(): array;
}
