<?php

namespace Stu\Module\Control;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;

interface GameControllerInterface
{

    public function setView(string $view, array $viewContext = []): void;

    public function getViewContext(): array;

    public function getGameState(): int;

    public function setTemplateFile(string $tpl): void;

    public function setMacro($macro): void;

    public function showMacro($macro): void;

    public function getMacro(): string;

    public function addInformationfWithLink(string $text, string $link, ...$args): void;

    public function addInformationf(string $text, ...$args): void;

    public function addInformationWithLink(string $msg, string $link, bool $override = false): void;

    public function addInformation(string $msg, bool $override = false): void;

    public function addInformationMerge(array $info): void;

    public function addInformationMergeDown(array $info): void;

    public function getInformation(): array;

    public function sendInformation(
        $recipient_id,
        $sender_id = GameEnum::USER_NOONE,
        $category_id = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
    );

    public function setTemplateVar(string $key, $variable);

    public function getUser(): ?UserInterface;

    public function getPlayerCount(): int;

    public function getGameConfig(): array;

    public function getUniqId(): string;

    public function appendNavigationPart(string $url, string $title): void;

    public function getNavigation(): array;

    public function getPageTitle(): string;

    public function setPageTitle(string $title): void;

    public function getQueryCount(): int;

    public function getExecuteJS(): array;

    public function addExecuteJS(string $value): void;

    public function redirectTo(string $href): void;

    public function getCurrentRound(): GameTurnInterface;

    public function isDebugMode(): bool;

    public function getJavascriptPath(): string;

    public function checkDatabaseItem($databaseEntryId): void;

    public function getAchievements(): array;

    public function getSessionString(): string;

    public function sessionAndAdminCheck(): void;

    public function main(array $actions, array $views, bool $session_check = true, bool $admin_check = false): void;

    public function isSemaphoreAlreadyAcquired(int $key): bool;

    public function addSemaphore(int $key, $semaphore): void;

    public function getGameStats(): array;

    public function getGameStateTextual(): string;

    public function getLoginError(): string;

    public function getBenchmarkResult(): array;

    public function getContactlistModes(): array;
}
