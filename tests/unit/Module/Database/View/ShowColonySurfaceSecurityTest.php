<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurface;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurfaceRequestInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;
use Stu\StuTestCase;

final class ShowColonySurfaceSecurityTest extends StuTestCase
{
    #[DataProvider('accessCases')]
    public function testAccess(bool $exists, int $ownerId, ?int $ownerAlliance, ?int $viewerAlliance, bool $allowed): void
    {
        $repository = $this->mock(ColonyScanRepositoryInterface::class);
        $request = $this->mock(ShowColonySurfaceRequestInterface::class);
        $game = $this->mock(ViewControllerContext::class);
        $viewer = $this->mock(User::class);
        $owner = $this->mock(User::class);
        $scan = $this->mock(ColonyScan::class);
        $viewer->shouldReceive('getId')->andReturn(101);
        $owner->shouldReceive('getId')->andReturn($ownerId);
        foreach ([[$viewer, $viewerAlliance], [$owner, $ownerAlliance]] as [$user, $id]) {
            $alliance = $id === null ? null : $this->mock(Alliance::class);
            $alliance?->shouldReceive('getId')->andReturn($id);
            $user->shouldReceive('getAlliance')->andReturn($alliance);
        }
        $scan->shouldReceive('getUser')->andReturn($owner);
        $request->shouldReceive('getId')->andReturn(7);
        $repository->shouldReceive('find')->with(7)->once()->andReturn($exists ? $scan : null);
        $game->shouldReceive('getUser')->andReturn($viewer);
        $game->shouldReceive('setPageTitle')->once();
        $game->shouldReceive('setMacroInAjaxWindow')->once();
        if ($allowed) {
            $repository->shouldReceive('getSurfaceArray')->with(7)->once()->andReturn(serialize([]));
            $repository->shouldReceive('getSurfaceWidth')->with(7)->once()->andReturn(2);
            $game->shouldReceive('setTemplateVar')->with('SURFACE', [])->once();
            $game->shouldReceive('setTemplateVar')->with('CSSCLASS', 'cfu')->once();
            $game->shouldReceive('setTemplateVar')->with('SURFACETILESTYLE', 'display: grid; grid-template-columns: 43px 43px;')->once();
        } else {
            $repository->shouldNotReceive('getSurfaceArray');
            $repository->shouldNotReceive('getSurfaceWidth');
            $this->expectException(AccessViolationException::class);
        }
        new ShowColonySurface($repository, $request)->handle($game);
    }

    public static function accessCases(): iterable
    {
        yield 'owner' => [true, 101, null, null, true];
        yield 'alliance' => [true, 102, 1, 1, true];
        yield 'foreign alliance' => [true, 102, 2, 1, false];
        yield 'no alliance' => [true, 102, null, null, false];
        yield 'former member' => [true, 102, null, 1, false];
        yield 'missing' => [false, 102, 1, 1, false];
    }
}
