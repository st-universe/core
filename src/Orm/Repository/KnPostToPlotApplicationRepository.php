<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPostToPlotApplication;
use Stu\Orm\Entity\KnPostToPlotApplicationInterface;

/**
 * @extends EntityRepository<KnPostToPlotApplication>
 */
final class KnPostToPlotApplicationRepository extends EntityRepository implements KnPostToPlotApplicationRepositoryInterface
{
    public function prototype(): KnPostToPlotApplicationInterface
    {
        return new KnPostToPlotApplication();
    }

    public function save(KnPostToPlotApplicationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(KnPostToPlotApplicationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByPostAndPlot(int $postId, int $plotId): ?KnPostToPlotApplicationInterface
    {
        return $this->findOneBy(
            [
                'post_id' => $postId,
                'plot_id' => $plotId,
            ]
        );
    }
}
