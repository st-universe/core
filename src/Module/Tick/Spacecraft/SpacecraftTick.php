<?php

namespace Stu\Module\Tick\Spacecraft;

use Override;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\Handler\SpacecraftTickHandlerInterface;
use Stu\Module\Tick\Spacecraft\ManagerComponent\ManagerComponentInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class SpacecraftTick implements SpacecraftTickInterface, ManagerComponentInterface
{
    /** @param array<SpacecraftTickHandlerInterface> $handlers */
    public function __construct(
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly InformationFactoryInterface $informationFactory,
        private readonly array $handlers
    ) {}

    #[Override]
    public function work(): void
    {
        foreach ($this->spacecraftRepository->getPlayerSpacecraftsForTick() as $spacecraft) {
            $this->workSpacecraft($this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft));
        }
    }

    #[Override]
    public function workSpacecraft(SpacecraftWrapperInterface $wrapper): void
    {
        $informationWrapper = $this->informationFactory->createInformationWrapper();

        $this->workSpacecraftInternal($wrapper, $informationWrapper);

        $this->sendMessage($wrapper->get(), $informationWrapper);
    }

    private function workSpacecraftInternal(SpacecraftWrapperInterface $wrapper, InformationWrapper $informationWrapper): void
    {
        $spacecraft = $wrapper->get();

        $startTime = microtime(true);

        try {

            foreach ($this->handlers as $handler) {
                $startTime = microtime(true);
                $handler->handleSpacecraftTick($wrapper, $informationWrapper);
                $this->potentialLog(
                    $spacecraft,
                    $handler,
                    $startTime
                );
            }
        } catch (SpacecraftTickFinishedException) {
            $this->potentialLog(
                $spacecraft,
                $handler,
                $startTime
            );
        }

        $this->spacecraftRepository->save($spacecraft);
    }

    private function potentialLog(Spacecraft $spacecraft, SpacecraftTickHandlerInterface $handler, float $startTime): void
    {
        $endTime = microtime(true);

        if (
            $endTime - $startTime > 0.1
        ) {

            $classParts = explode('\\', get_class($handler));

            StuLogger::log(sprintf(
                "\t\t\t%d: %s, seconds: %F",
                $spacecraft->getId(),
                end($classParts),
                $endTime - $startTime
            ), LogTypeEnum::TICK);
        }
    }

    private function sendMessage(Spacecraft $ship, InformationWrapper $informationWrapper): void
    {
        if ($informationWrapper->isEmpty()) {
            return;
        }

        $text = sprintf("Tickreport der %s\n%s", $ship->getName(), $informationWrapper->getInformationsAsString());

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $ship->getUser()->getId(),
            $text,
            $ship->getType()->getMessageFolderType(),
            $ship
        );
    }
}
