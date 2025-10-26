<?php

namespace Stu\Module\Maintenance;

use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserInformation implements MaintenanceHandlerInterface
{
    private const FOUR_MONTHS_IN_SECONDS = 4 * 30 * 24 * 60 * 60; // 4 Monate
    private const FIVE_MONTHS_IN_SECONDS = 5 * 30 * 24 * 60 * 60; // 5 Monate
    private const ONE_DAY_IN_SECONDS = 24 * 60 * 60;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(): void
    {
        $currentTime = $this->stuTime->time();

        $fourMonthsAgo = $currentTime - self::FOUR_MONTHS_IN_SECONDS;
        $fourMonthsPlusOneDayAgo = $currentTime - self::FOUR_MONTHS_IN_SECONDS - self::ONE_DAY_IN_SECONDS;

        $fiveMonthsAgo = $currentTime - self::FIVE_MONTHS_IN_SECONDS;
        $fiveMonthsPlusOneDayAgo = $currentTime - self::FIVE_MONTHS_IN_SECONDS - self::ONE_DAY_IN_SECONDS;

        foreach ($this->userRepository->getNonNpcList() as $user) {
            $creationTime = $user->getRegistration()->getCreationDate();

            if ($creationTime <= $fourMonthsAgo && $creationTime > $fourMonthsPlusOneDayAgo) {
                $noobzoneLayerNames = $this->getNoobzoneLayerNames($user);

                if (!empty($noobzoneLayerNames)) {
                    $layerNamesString = implode(', ', $noobzoneLayerNames);

                    $message = sprintf(
                        "Du bist jetzt 4 Monate bei STU dabei. Deine Großmacht gestattet dir noch einen Monat neue Kolonien im Newbie Gebiet %s zu gründen. Such dir vielleicht schonmal Kolonien auf einer anderen Karte. Deine Kolonien bleiben natürlich auch danach bestehen, solang du sie nicht aufgibst.",
                        $layerNamesString
                    );

                    $this->privateMessageSender->send(
                        UserConstants::USER_NOONE,
                        $user->getId(),
                        $message,
                        PrivateMessageFolderTypeEnum::SPECIAL_MAIN
                    );
                }
            }

            if ($creationTime <= $fiveMonthsAgo && $creationTime > $fiveMonthsPlusOneDayAgo) {
                $noobzoneLayerNames = $this->getNoobzoneLayerNames($user);

                if (!empty($noobzoneLayerNames)) {
                    $layerNamesString = implode(', ', $noobzoneLayerNames);

                    $message = sprintf(
                        "Du kannst ab sofort keine neuen Kolonien mehr im Newbie Gebiet %s kolonisieren. Deine bestehenden Kolonien bleiben erhalten, bis du sie aufgibst.",
                        $layerNamesString
                    );

                    $this->privateMessageSender->send(
                        UserConstants::USER_NOONE,
                        $user->getId(),
                        $message,
                        PrivateMessageFolderTypeEnum::SPECIAL_MAIN
                    );
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    private function getNoobzoneLayerNames(User $user): array
    {
        $noobzoneLayerNames = [];

        foreach ($user->getColonies() as $colony) {
            $system = $colony->getSystem();
            $layer = $system->getLayer();

            if ($layer !== null && $layer->isNoobzone()) {
                $layerName = $layer->getName();

                if (!in_array($layerName, $noobzoneLayerNames, true)) {
                    $noobzoneLayerNames[] = $layerName;
                }
            }
        }

        return $noobzoneLayerNames;
    }
}
