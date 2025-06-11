<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\CheckInput;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\NoAccessCheckControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CheckInput implements ActionControllerInterface, NoAccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHECK_REGVAR';
    public const string REGISTER_STATE_OK = "OK";
    public const string REGISTER_STATE_NOK = "NA";
    public const string REGISTER_STATE_DUP = "DUP"; //duplication
    public const string REGISTER_STATE_UCP = "UCP"; //unknown country prefix
    public const string REGISTER_STATE_UPD = "UPD"; //unknown phone digits
    public const string REGISTER_STATE_BLK = "BLK";

    public function __construct(private CheckInputRequestInterface $checkInputRequest, private UserRepositoryInterface $userRepository, private BlockedUserRepositoryInterface $blockedUserRepository, private StuHashInterface $stuHash) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $var = $this->checkInputRequest->getVariable();
        $value =  trim($this->checkInputRequest->getValue());
        $state = self::REGISTER_STATE_NOK;

        $currentUser = $game->hasUser() ? $game->getUser() : null;

        switch ($var) {
            default:
                break;
            case 'loginname':
                if (!preg_match('=^[a-zA-Z0-9]+$=i', $value)) {
                    break;
                }
                if (strlen($value) < 6) {
                    break;
                }
                if ($this->userRepository->getByLogin($value) !== null) {
                    $state = self::REGISTER_STATE_DUP;
                    break;
                }
                $state = self::REGISTER_STATE_OK;
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    break;
                }
                $existingUser = $this->userRepository->getByEmail($value);
                if ($existingUser !== null && ($currentUser === null || $existingUser->getId() !== $currentUser->getId())) {
                    $state = self::REGISTER_STATE_DUP;
                    break;
                }
                if ($this->blockedUserRepository->getByEmailHash($this->stuHash->hash($value)) !== null) {
                    $state = self::REGISTER_STATE_BLK;
                    break;
                }
                $state = self::REGISTER_STATE_OK;
                break;
            case 'mobile':
                $trimmedMobile = str_replace('+', '00', str_replace(' ', '', trim($value, " \t\n\r\x0B")));
                $trimmedMobileHash = $this->stuHash->hash($trimmedMobile);

                if (!$this->isMobileNumberCountryAllowed($trimmedMobile)) {
                    $state = self::REGISTER_STATE_UCP;
                    break;
                }
                if (!$this->isMobileFormatCorrect($trimmedMobile)) {
                    $state = self::REGISTER_STATE_UPD;
                    break;
                }
                $existingUser = $this->userRepository->getByMobile($trimmedMobile, $trimmedMobileHash);
                if ($existingUser !== null && ($currentUser === null || $existingUser->getId() !== $currentUser->getId())) {
                    $state = self::REGISTER_STATE_DUP;
                    break;
                }
                if ($this->blockedUserRepository->getByMobileHash($trimmedMobileHash) !== null) {
                    $state = self::REGISTER_STATE_BLK;
                    break;
                }
                $state = self::REGISTER_STATE_OK;
                break;
        }
        echo $state;
        exit;
    }

    private function isMobileNumberCountryAllowed(string $mobile): bool
    {
        return strpos($mobile, '0049') === 0 || strpos($mobile, '0041') === 0 || strpos($mobile, '0043') === 0;
    }

    private function isMobileFormatCorrect(string $mobile): bool
    {
        return (bool) preg_match('/00..[1-9]\d/', $mobile);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
