<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Override;
use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;

class CommodityTransferStrategy implements TransferStrategyInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private StatusBarFactoryInterface $statusBarFactory
    ) {}

    #[Override]
    public function setTemplateVariables(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $targetWrapper,
        GameControllerInterface $game
    ): void {

        $target = $targetWrapper->get();
        $beamableStorage = $isUnload ? $source->get()->getBeamableStorage() : $target->getBeamableStorage();

        $game->setTemplateVar(
            'BEAMABLE_STORAGE',
            $beamableStorage
        );

        if ($target instanceof ColonyInterface) {
            $game->setTemplateVar(
                'SHOW_SHIELD_FREQUENCY',
                $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled() && $target->getUser() !== $source->getUser()
            );
        }

        $game->setTemplateVar('SOURCE_STORAGE_BAR', $this->createStorageBar($source));
        $game->setTemplateVar('TARGET_STORAGE_BAR', $this->createStorageBar($targetWrapper));
    }

    private function createStorageBar(StorageEntityWrapperInterface $entityWrapper): string
    {
        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Lager'))
            ->setMaxValue($entityWrapper->get()->getMaxStorage())
            ->setValue($entityWrapper->get()->getStorageSum())
            ->render();
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $from = $isUnload ? $source : $target;
        if ($from->get()->getBeamableStorage()->isEmpty()) {
            $information->addInformation('Keine Waren zum Beamen vorhanden');
            return;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if (count($commodities) == 0 || count($gcount) == 0) {
            $information->addInformation("Es wurden keine Waren zum Beamen ausgewählt");
            return;
        }

        if ($this->shieldsAreBlocking($target, $source->getUser(), $information)) {
            return;
        }

        $destination = $isUnload ? $target : $source;
        if ($destination->get()->getStorageSum() >= $destination->get()->getMaxStorage()) {
            $information->addInformationf('Der Lagerraum der %s ist voll', $destination->getName());
            return;
        }

        $source->transfer($isUnload, $target, $information);
    }

    private function shieldsAreBlocking(StorageEntityWrapperInterface $targetWrapper, UserInterface $user, InformationInterface $information): bool
    {
        $target = $targetWrapper->get();

        if (
            $target instanceof ColonyInterface
            && $target->getUser() !== $user
            && $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled()
            && $target->getShieldFrequency()
        ) {
            $frequency = request::postInt('frequency');
            if ($frequency !== $target->getShieldFrequency()) {
                $information->addInformation("Die Schildfrequenz ist nicht korrekt");
                return true;
            }
        }

        return false;
    }
}
