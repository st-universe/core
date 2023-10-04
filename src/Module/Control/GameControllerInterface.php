<?php

namespace Stu\Module\Control;

use Stu\Component\Game\GameEnum;
use Stu\Lib\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;

interface GameControllerInterface
{
    public function setView(string $view, array $viewContext = []): void;

    public function getViewContext(): array;

    public function getGameState(): int;

    public function setTemplateFile(string $tpl, bool $isTwig = false): void;

    public function setMacroAndTemplate(string $macro, string $tpl): void;

    public function setMacroInAjaxWindow(string $macro, bool $isTwig = false): void;

    public function showMacro(string $macro, bool $isTwig = false): void;

    public function getMacro(): string;

    public function addInformationfWithLink(string $text, string $link, ...$args): void;

    public function addInformationf(string $text, ...$args): void;

    public function addInformationWithLink(string $msg, string $link, bool $override = false): void;

    public function addInformation(string $msg, bool $override = false): void;

    public function addInformationMerge(array $info): void;

    public function addInformationMergeDown(array $info): void;

    public function addInformationWrapper(?InformationWrapper $informations, bool $isHead = false): void;

    public function getInformation(): array;

    public function sendInformation(
        $recipient_id,
        $sender_id = UserEnum::USER_NOONE,
        $category_id = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        ?string $href = null
    );

    public function setTemplateVar(string $key, $variable);

    public function getUser(): UserInterface;

    public function hasUser(): bool;

    public function getGameConfig(): array;

    public function getUniqId(): string;

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

    /** @return array<string> */
    public function getExecuteJS(): array;

    /** @return array<string> */
    public function getExecuteJsAfterRender(): array;

    /** @return array<string> */
    public function getExecuteJsAjaxUpdate(): array;

    public function addExecuteJS(string $value, int $when = GameEnum::JS_EXECUTION_BEFORE_RENDER): void;

    public function redirectTo(string $href): void;

    public function getCurrentRound(): GameTurnInterface;

    public function getJavascriptPath(): string;

    public function checkDatabaseItem($databaseEntryId): void;

    public function getAchievements(): array;

    public function getSessionString(): string;

    public function sessionAndAdminCheck(): void;


    public function getGameRequest(): GameRequestInterface;

    public function getGameRequestId(): string;

    /**
     * @param array<string, ActionControllerInterface> $actions
     * @param array<string, ViewControllerInterface> $views
     */
    public function main(
        string $module,
        array $actions,
        array $views,
        bool $session_check = true,
        bool $admin_check = false
    ): void;

    public function isAdmin(): bool;

    public function isSemaphoreAlreadyAcquired(int $key): bool;

    public function addSemaphore(int $key, $semaphore): void;

    public function triggerEvent(object $event): void;

    public function getGameStats(): array;

    public function getGameStateTextual(): string;

    public function getLoginError(): string;

    public function getBenchmarkResult(): array;

    public function getContactlistModes(): array;
}
