<?php

namespace Stu\Control;

use Tuple;
use UserData;

interface GameControllerInterface
{

    public function setView(string $view): void;

    public function getGameState();

    public function setTemplateFile($tpl);

    public function setAjaxMacro($macro);

    public function showAjaxMacro($macro);

    public function getAjaxMacro();

    public function getMemoryUsage();

    public function addInformation(string $msg, bool $override = false): void;

    function getInformation();

    public function hasInformation();

    public function setTemplateVar(string $key, $variable);

    public function getUser(): ?UserData;

    public function getBenchmark();

    public function getPlayerCount();

    public function getGameConfig();

    public function getGameConfigValue($value);

    public function getUniqHandle();

    function addNavigationPart(Tuple $part);

    public function appendNavigationPart(string $url, string $title);

    public function getNavigation();

    public function getPageTitle();

    public function setPageTitle($title);

    public function getQueryCount();

    public function getDebugNotices();

    public function hasExecuteJS();

    public function getExecuteJS();

    public function getGameVersion();

    public function redirectTo(string $href): void;

    public function getCurrentRound();

    public function isDebugMode();

    public function getJavascriptPath();

    public function getPlanetColonyLimit();

    public function getMoonColonyLimit();

    public function getPlanetColonyCount();

    public function getMoonColonyCount();

    public function isAdmin();

    public function checkDatabaseItem($databaseEntryId): void;

    public function getAchievements();

    public function getSessionString(): string;

    public function main(bool $session_check = true): void;

    public function isRegistrationPossible(): bool;

    public function getGameStats(): array;

    public function setLoginError(string $error): void;

    public function getLoginError(): string;
}