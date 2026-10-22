<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\ProtectedMutationTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class CreateRelationSecurityTest extends ProtectedMutationTestCase
{
    public function testRejectsForgedRequests(): void
    {
        $this->assertRequestProtection(new CreateRelation(
            $this->mock(CreateRelationRequestInterface::class),
            $this->mock(RelationRepositoryInterface::class),
            $this->mock(RelationPermissionRepositoryInterface::class),
            $this->mock(UserRelationManagerInterface::class),
            $this->mock(AllianceJobManagerInterface::class),
            $this->mock(AllianceRepositoryInterface::class),
            $this->mock(EventDispatcherInterface::class)
        ));
    }

    #[DataProvider('permissions')]
    public function testOnlyAuthorizedOfficerCanDeclareWarForOwnAlliance(bool $allowed): void
    {
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $alliance->shouldReceive('getId')->andReturn(7);
        $user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->twice()->andReturn($user);
        $permissions = $this->mock(AllianceJobManagerInterface::class);
        $permissions->shouldReceive('hasUserPermission')->with($user, $alliance, AllianceJobPermissionEnum::CREATE_AGREEMENTS)->once()->andReturn($allowed);
        $request = $this->mock(CreateRelationRequestInterface::class);
        $relations = $this->mock(RelationRepositoryInterface::class);
        $alliances = $this->mock(AllianceRepositoryInterface::class);
        $dispatcher = $this->mock(EventDispatcherInterface::class);
        if ($allowed) {
            $counterpart = $this->mock(Alliance::class);
            $counterpart->shouldReceive('getId')->andReturn(8);
            $counterpart->shouldReceive('getName')->once()->andReturn('Andere Allianz');
            $request->shouldReceive('getCounterpartId')->once()->andReturn(8);
            $request->shouldReceive('getRelationType')->once()->andReturn(AllianceRelationTypeEnum::WAR->value);
            $request->shouldReceive('getPermissions')->once()->andReturn(0);
            $alliances->shouldReceive('find')->with(8)->once()->andReturn($counterpart);
            $relations->shouldReceive('getPendingCountByAlliances')->with(7, 8)->once()->andReturn(0);
            $relations->shouldReceive('getByAlliancePair')->with(7, 8)->once()->andReturn([]);
            $dispatcher->shouldReceive('dispatch')->once()->withArgs(static fn (WarDeclaredEvent $event): bool => $event->getAlliance() === $alliance && $event->getCounterpart() === $counterpart && $event->getResponsibleUser() === $user);
            $info = $this->mock(InformationWrapper::class);
            $info->shouldReceive('addInformation')->once();
            $context->shouldReceive('getInfo')->once()->andReturn($info);
        } else {
            $this->expectException(AccessViolationException::class);
        }
        new CreateRelation($request, $relations, $this->mock(RelationPermissionRepositoryInterface::class), $this->mock(UserRelationManagerInterface::class), $permissions, $alliances, $dispatcher)->handle($context);
    }

    public function testAllDiplomacyPostFormsCarryActionToken(): void
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../../../src'), ['autoescape' => 'html']);
        $twig->addFunction(new \Twig\TwigFunction('hasAlliancePermission', static fn (): bool => true));
        $twig->addFilter(new \Twig\TwigFilter('bbcode', static fn (string $value): string => $value));
        $twig->addFilter(new \Twig\TwigFilter('bbcode2txt', static fn (string $value): string => $value));
        $twig->addFilter(new \Twig\TwigFilter('stuDateTime', static fn (): string => 'Datum'));
        $relation = new \Stu\Orm\Entity\Relation()->setType(AllianceRelationTypeEnum::FRIENDS)->setDate(1);
        $reflection = new \ReflectionProperty($relation, 'id');
        $reflection->setValue($relation, 9);
        $counterpart = $this->mock(Alliance::class);
        $counterpart->shouldReceive('getId')->andReturn(8);
        $counterpart->shouldReceive('getName')->andReturn('Andere Allianz');
        $document = HTMLDocument::createFromString($twig->render('html/alliance/alliancerelations.twig', ['SESSIONSTRING' => 'own-token', 'POSSIBLE_RELATION_TYPES' => [], 'ALLIANCE_LIST' => [], 'RELATION_PERMISSIONS' => [], 'USER_RELATIONS' => [], 'RELATIONS' => [['getOpponent' => $counterpart, 'getRelation' => $relation]]]), LIBXML_NOERROR);
        $forms = $document->querySelectorAll('form[method="post"]');
        self::assertGreaterThanOrEqual(2, $forms->length);
        foreach ($forms as $form) {
            self::assertSame('own-token', $form->querySelector('input[name="sstr"]')?->getAttribute('value'));
        }
    }

    public static function permissions(): iterable
    {
        yield 'officer' => [true];
        yield 'ordinary member' => [false];
    }
}
