<?php

namespace Stu\Module\Control;

use GameTurnData;
use UserData;

interface GameControllerInterface
{

    public function setView(string $view, array $viewContext = []): void;

    public function getViewContext(): array;

    public function getGameState(): string;

    public function setTemplateFile(string $tpl): void;

    public function setMacro($macro): void;

    public function showMacro($macro): void;

    public function getMacro(): string;

    public function getMemoryUsage();

    public function addInformationf(string $text, ...$args): void;

    public function addInformation(string $msg, bool $override = false): void;

    public function addInformationMerge(array $info): void;

    public function addInformationMergeDown(array $info): void;

    public function getInformation(): array;

    public function sendInformation($recipient_id, $sender_id = USER_NOONE, $category_id = PM_SPECIAL_MAIN);

    public function setTemplateVar(string $key, $variable);

    public function getUser(): ?UserData;

    public function getBenchmark(): float;

    public function getPlayerCount(): int;

    public function getGameConfig(): array;

    public function getUniqId(): string;

    public function appendNavigationPart(string $url, string $title): void;

    public function getNavigation(): array;

    public function getPageTitle(): string;

    public function setPageTitle(string $title): void;

    public function getQueryCount(): int;

    public function getDebugNotices(): array;

    public function getExecuteJS(): array;

    public function addExecuteJS(string $value): void;

    public function getGameVersion(): string;

    public function redirectTo(string $href): void;

    public function getCurrentRound(): GameTurnData;

    public function isDebugMode(): bool;

    public function getJavascriptPath(): string;

    public function getPlanetColonyLimit(): int;

    public function getMoonColonyLimit(): int;

    public function getPlanetColonyCount(): int;

    public function getMoonColonyCount(): int;

    public function isAdmin(): bool;

    public function checkDatabaseItem($databaseEntryId): void;

    public function getAchievements(): array;

    public function getSessionString(): string;

    public function main(array $actions, array $views, bool $session_check = true): void;

    public function isRegistrationPossible(): bool;

    public function getGameStats(): array;

    public function getGameStateTextual(): string;

    public function setLoginError(string $error): void;

    public function getLoginError(): string;
}