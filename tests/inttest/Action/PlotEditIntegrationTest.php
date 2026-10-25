<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\CommunicationMutationIntegrationTestCase;
use Stu\Module\Communication\Action\EditKnPlot\EditKnPlot;
use Stu\Module\Communication\Action\ReopenKnPlot\ReopenKnPlot;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlotEditIntegrationTest extends CommunicationMutationIntegrationTestCase
{
    public static function actions(): iterable
    {
        yield 'EditKnPlot' => ['Stu\\Module\\Communication\\Action\\EditKnPlot\\EditKnPlot'];
        yield 'ReopenKnPlot' => [ReopenKnPlot::class];
    }

    #[DataProvider('actions')]
    public function testForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks(string $class): void
    {
        if ($class === ReopenKnPlot::class) {
            $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
            $entityManager->find(RpgPlot::class, $this->ids['plot'])->setEndDate(time() - 3600);
            $entityManager->flush();
        }
        $this->assertForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks($class);
    }

    public function testOwnTokenCannotChangeForeignObjects(): void
    {
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $entityManager->find(RpgPlot::class, $this->ids['foreignPlot'])->setEndDate(time() - 3600);
        $entityManager->flush();
        $this->assertOwnTokenCannotChangeForeignObjects();
    }

    #[DataProvider('plotChanges')]
    public function testOwnerCanChangeOldPlotAndNotifyOtherMembers(string $action, bool $closed): void
    {
        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $plot = $entityManager->find(RpgPlot::class, $this->ids['plot']);
        $plot->setStartDate(time() - 365 * 86400);
        $endDate = $closed ? time() - 3600 : null;
        $plot->setEndDate($endDate);
        $plot->setEditDate(123);
        foreach ([101, 102, 103] as $userId) {
            $user = $container->get(UserRepositoryInterface::class)->find($userId);
            $member = new RpgPlotMember()->setUser($user)->setRpgPlot($plot);
            $entityManager->persist($member);
            $plot->getMembers()->set($userId, $member);
        }
        $entityManager->flush();

        $parameters = $this->parameters($action);
        $this->dispatch($action, 'POST', $parameters + ['sstr' => $this->token(101)]);
        $entityManager->refresh($plot);

        self::assertSame(1732214228, $plot->getEditDate());
        self::assertSame($action === ReopenKnPlot::class ? null : $endDate, $plot->getEndDate());
        if ($action === EditKnPlot::class) {
            self::assertSame('Neuer Plottitel', $plot->getTitle());
            self::assertSame('Neue Beschreibung', $plot->getDescription());
        } else {
            self::assertContains($plot, $container->get(RpgPlotRepositoryInterface::class)->getActiveByUser(102));
        }

        $href = sprintf('/communication.php?SHOW_PLOT=1&plotid=%d', $plot->getId());
        $messages = $entityManager->getRepository(PrivateMessage::class)->findBy(['href' => $href]);
        self::assertCount(2, $messages);
        $recipients = array_map(static fn (PrivateMessage $message): int => $message->getRecipient()->getId(), $messages);
        self::assertEqualsCanonicalizing([102, 103], $recipients);
        foreach ($messages as $message) {
            self::assertStringContainsString($plot->getTitle(), $message->getText());
            self::assertStringContainsString($action === ReopenKnPlot::class ? 'erneut eröffnet' : 'bearbeitet', $message->getText());
        }

        $this->dispatch($action, 'POST', $parameters + ['sstr' => $this->token(101)]);
        self::assertCount(2, $entityManager->getRepository(PrivateMessage::class)->findBy(['href' => $href]));
    }

    public static function plotChanges(): iterable
    {
        yield 'edit active plot' => [EditKnPlot::class, false];
        yield 'edit ended plot' => [EditKnPlot::class, true];
        yield 'reopen ended plot' => [ReopenKnPlot::class, true];
    }

    #[DataProvider('invalidTitles')]
    public function testInvalidTitleLeavesPlotAndEditDateUnchanged(string $title): void
    {
        $before = $this->state();
        $this->dispatch(EditKnPlot::class, 'POST', [
            'plotid' => $this->ids['plot'],
            'title' => $title,
            'description' => 'Ungültige Änderung',
            'sstr' => $this->token(101)
        ]);
        self::assertSame($before, $this->state());
    }

    public static function invalidTitles(): iterable
    {
        yield 'too short' => ['kurz'];
        yield 'too long' => [str_repeat('ä', 81)];
    }
}
