<?php

namespace Stu\Component\Spacecraft\System\Control;

use Stu\Component\Spacecraft\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\AlreadyActiveException;
use Stu\Component\Spacecraft\System\Exception\InsufficientCrewException;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\Exception\SystemCooldownException;
use Stu\Component\Spacecraft\System\Exception\SystemDamagedException;
use Stu\Component\Spacecraft\System\Exception\SystemNotActivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Template\TemplateHelperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class SystemActivation
{
    public function __construct(
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly TemplateHelperInterface $templateHelper
    ) {}

    public function activateIntern(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $isDryRun
    ): bool {
        $systemName = $type->getDescription();
        $spacecraft = $wrapper->get();

        try {
            $this->spacecraftSystemManager->activate($wrapper, $type, false, $isDryRun);
            $this->spacecraftRepository->save($spacecraft);
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s aktiviert'), $spacecraft->getName(), $systemName);
            }
            return true;
        } catch (AlreadyActiveException) {
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s ist bereits aktiviert'), $spacecraft->getName(), $systemName);
            }
        } catch (SystemNotActivatableException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s besitzt keinen Aktivierungsmodus[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (InsufficientEnergyException $e) {
            $this->logError($spacecraft, sprintf(
                _('%s: [b][color=#ff2626]System %s kann aufgrund Energiemangels (%d benötigt) nicht aktiviert werden[/color][/b]'),
                $spacecraft->getName(),
                $systemName,
                $e->getNeededEnergy()
            ), $logger);
        } catch (SystemCooldownException $e) {
            $this->logError($spacecraft, sprintf(
                _('%s: [b][color=#ff2626]System %s kann nicht aktiviert werden, Cooldown noch %s[/color][/b]'),
                $spacecraft->getName(),
                $systemName,
                $this->templateHelper->formatSeconds((string) $e->getRemainingSeconds())
            ), $logger);
        } catch (SystemDamagedException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s ist beschädigt und kann daher nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (ActivationConditionsNotMetException $e) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden, weil %s[/color][/b]'), $spacecraft->getName(), $systemName, $e->getMessage()), $logger);
        } catch (SystemNotFoundException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s nicht vorhanden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (InsufficientCrewException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte wegen Mangel an Crew nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (SpacecraftSystemException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        }

        return false;
    }

    private function logError(SpacecraftInterface $spacecraft, string $message, ConditionCheckResult|InformationInterface $logger): void
    {
        if ($logger instanceof InformationInterface) {
            $logger->addInformation($message);
        } elseif ($spacecraft instanceof ShipInterface) {
            $logger->addBlockedShip($spacecraft, $message);
        }
    }
}
