<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\Module\Maintenance\EmptyPlotDeletion;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\RpgPlot;
use Stu\TestSession;

class RpgPlotRepositoryTest extends IntegrationTestCase
{
    public function testGetEmptyOldPlotsProtectsPlotsOfUnfinishedQuests(): void
    {
        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $repository = $container->get(RpgPlotRepositoryInterface::class);
        $user = $container->get(UserRepositoryInterface::class)->find(TestSession::DEFAULT_USER_ID);
        self::assertNotNull($user);

        $now = time();
        $maxAge = EmptyPlotDeletion::MAX_AGE_IN_SECONDS;
        $plots = [];
        foreach (['withoutQuest', 'activeQuest', 'upcomingQuest', 'finishedQuest', 'multipleQuests', 'recent', 'withPost'] as $name) {
            $plot = new RpgPlot()
                ->setUser($user)
                ->setTitle($name)
                ->setStartDate($name === 'recent' ? $now : $now - $maxAge - 3600);
            $repository->save($plot);
            $plots[$name] = $plot;
        }

        $quests = [];
        foreach (['activeQuest', 'upcomingQuest', 'finishedQuest', 'multipleQuests', 'multipleQuests'] as $index => $name) {
            $quest = new NPCQuest()
                ->setUser($user)
                ->setPlot($plots[$name])
                ->setStart($name === 'upcomingQuest' ? $now + 3600 : $now - 3600)
                ->setEnd($name === 'finishedQuest' || $index === 3 ? $now - 60 : null);
            $entityManager->persist($quest);
            $quests[] = $quest;
        }

        $post = new KnPost()->setUser($user)->setRpgPlot($plots['withPost']);
        $entityManager->persist($post);
        $entityManager->flush();

        try {
            $result = $repository->getEmptyOldPlots($maxAge);

            self::assertContains($plots['withoutQuest'], $result);
            self::assertContains($plots['finishedQuest'], $result);
            self::assertNotContains($plots['activeQuest'], $result);
            self::assertNotContains($plots['upcomingQuest'], $result);
            self::assertNotContains($plots['multipleQuests'], $result);
            self::assertNotContains($plots['recent'], $result);
            self::assertNotContains($plots['withPost'], $result);
        } finally {
            foreach ($quests as $quest) {
                $entityManager->remove($quest);
            }
            $entityManager->remove($post);
            foreach ($plots as $plot) {
                $repository->delete($plot);
            }
            $entityManager->flush();
        }
    }
}
