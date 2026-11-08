<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use ReflectionProperty;
use request;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereAttackGuardTest extends StuTestCase
{
    public function testConfirmationIsBoundToTheTargetAndCannotBeReused(): void
    {
        $source = $this->ship(101);
        $target = $this->ship(102);
        $other = $this->ship(103);
        $repository = $this->mock(BorgSphereRepositoryInterface::class);
        $repository->shouldReceive('getByShip')->andReturn(new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::REGENERATING));
        $repository->shouldReceive('lockEvent');
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        $attackContext = new BorgSphereAttackContext();
        $combat = new BorgSphereCombat($repository, $attackContext, $time);
        $session = $this->mock(SessionStorageInterface::class);
        $data = [];
        $session->shouldReceive('getSessionValue')->andReturnUsing(static function (string $key) use (&$data): mixed {
            return $data[$key] ?? false;
        });
        $session->shouldReceive('deleteSessionData')->andReturnUsing(static function (string $key) use (&$data): void {
            unset($data[$key]);
        });
        $session->shouldReceive('storeSessionData')->andReturnUsing(static function (string $key, mixed $value) use (&$data): void {
            $data[$key] = $value;
        });
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView');
        $context->shouldReceive('addExecuteJS');
        $context->shouldReceive('getSessionString')->andReturn('session');
        $guard = new BorgSphereAttackGuard($combat, $attackContext, $session, $time);

        request::setMockVars([]);
        self::assertFalse($guard->prepare($source, $target, $context));
        $nonce = $data['borg_sphere_attack_101_102']['nonce'];
        request::setMockVars(['borg_confirmation' => $nonce]);
        self::assertFalse($guard->prepare($source, $other, $context));
        self::assertTrue($guard->prepare($source, $target, $context));
        self::assertTrue($attackContext->allowsDestruction($target));
        self::assertFalse($attackContext->allowsDestruction($other));
        $guard->finish();
        self::assertFalse($attackContext->allowsDestruction($target));
        self::assertFalse($guard->prepare($source, $target, $context));
    }

    private function ship(int $id): Ship
    {
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setCondition(new SpacecraftCondition($ship)->setDisabled(true));
        return $ship;
    }
}
