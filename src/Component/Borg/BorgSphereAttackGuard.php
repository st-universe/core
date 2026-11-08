<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;

final class BorgSphereAttackGuard
{
    public function __construct(
        private BorgSphereCombat $combat,
        private BorgSphereAttackContext $attackContext,
        private SessionStorageInterface $session,
        private StuTime $time
    ) {}

    public function prepare(Spacecraft $source, Spacecraft $target, ActionControllerContext $context): bool
    {
        if ($this->combat->getEvent($target) === null) {
            return true;
        }
        $disabled = $target->getCondition()->isDisabled();
        if ($disabled && !$this->confirm($source, $target, $context)) {
            return false;
        }
        $this->attackContext->begin($target, $disabled, request::getString('borg_target_system') === 'transwarp');
        $this->combat->registerHit($target);
        return true;
    }

    private function confirm(Spacecraft $source, Spacecraft $target, ActionControllerContext $context): bool
    {
        $key = sprintf('borg_sphere_attack_%d_%d', $source->getId(), $target->getId());
        $stored = $this->session->getSessionValue($key);
        $provided = request::getString('borg_confirmation') ?: '';
        $this->session->deleteSessionData($key);
        if (is_array($stored) && isset($stored['nonce'], $stored['expires'])
            && is_string($stored['nonce']) && is_int($stored['expires'])
            && $stored['expires'] >= $this->time->time() && hash_equals($stored['nonce'], $provided)) {
            return true;
        }
        $nonce = bin2hex(random_bytes(24));
        $this->session->storeSessionData($key, ['nonce' => $nonce, 'expires' => $this->time->time() + 300], true);
        $parameters = http_build_query([
            'id' => $source->getId(), 'target' => $target->getId(),
            'sstr' => $context->getSessionString(), 'borg_confirmation' => $nonce,
            'borg_target_system' => request::getString('borg_target_system')
        ]);
        $message = 'Die Sphäre ist nun deaktiviert. Möchtest du wirklich weiter darauf schießen? Sie kann dabei zerstört werden. Danach kann niemand mehr Naniten von ihr sammeln';
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $context->addExecuteJS(sprintf(
            'if (confirm(%s)) { actionToInnerContent("B_ATTACK_SPACECRAFT", %s); }',
            json_encode($message, JSON_THROW_ON_ERROR),
            json_encode($parameters, JSON_THROW_ON_ERROR)
        ), JavascriptExecutionTypeEnum::AFTER_RENDER);
        return false;
    }

    public function finish(): void
    {
        $this->attackContext->clear();
    }
}
