<?php

namespace Stu\Component\Spacecraft\System\Control;

use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class SystemDeactivation
{
    public function __construct(
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly SpacecraftRepositoryInterface $spacecraftRepository
    ) {}

    public function deactivateIntern(
        SpacecraftWrapperInterface $wrapper,
        spacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $systemName = $type->getDescription();
        $ship = $wrapper->get();

        try {
            $this->spacecraftSystemManager->deactivate($wrapper, $type);
            $this->spacecraftRepository->save($ship);
            $informations->addInformationf(_('%s: System %s deaktiviert'), $ship->getName(), $systemName);
            return true;
        } catch (AlreadyOffException) {
            $informations->addInformationf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName);
        } catch (SystemNotDeactivatableException) {
            $informations->addInformationf(_('%s: [b][color=#ff2626]System %s besitzt keinen Deaktivierungsmodus[/color][/b]'), $ship->getName(), $systemName);
        } catch (DeactivationConditionsNotMetException $e) {
            $informations->addInformationf(_('%s: [b][color=#ff2626]System %s konnte nicht deaktiviert werden, weil %s[/color][/b]'), $ship->getName(), $systemName, $e->getMessage());
        } catch (SystemNotFoundException) {
            $informations->addInformationf(_('%s: System %s nicht vorhanden'), $ship->getName(), $systemName);
        }

        return false;
    }
}
