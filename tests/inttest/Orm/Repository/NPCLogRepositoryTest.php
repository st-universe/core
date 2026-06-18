<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;

class NPCLogRepositoryTest extends IntegrationTestCase
{
    public function testGetByFactionAndSearchFiltersBySourceUserId(): void
    {
        $dic = $this->getContainer();
        $entityManager = $dic->get(EntityManagerInterface::class);
        $repository = $dic->get(NPCLogRepositoryInterface::class);

        $matchingLog = $repository->prototype()
            ->setText('needle source user')
            ->setDate(123)
            ->setSourceUserId(4242);

        $sameUserOtherTextLog = $repository->prototype()
            ->setText('created by source user')
            ->setDate(124)
            ->setSourceUserId(4242);

        $otherUserMatchingTextLog = $repository->prototype()
            ->setText('needle source user')
            ->setDate(124)
            ->setSourceUserId(4343);

        $repository->save($matchingLog);
        $repository->save($sameUserOtherTextLog);
        $repository->save($otherUserMatchingTextLog);
        $entityManager->flush();

        try {
            $result = $repository->getByFactionAndSearch(null, 10, '', 4242);

            static::assertContains($matchingLog, $result);
            static::assertContains($sameUserOtherTextLog, $result);
            static::assertNotContains($otherUserMatchingTextLog, $result);

            $result = $repository->getByFactionAndSearch(null, 10, 'needle', 4242);

            static::assertContains($matchingLog, $result);
            static::assertNotContains($sameUserOtherTextLog, $result);
            static::assertNotContains($otherUserMatchingTextLog, $result);
        } finally {
            $repository->delete($matchingLog);
            $repository->delete($sameUserOtherTextLog);
            $repository->delete($otherUserMatchingTextLog);
            $entityManager->flush();
        }
    }
}
