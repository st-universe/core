<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPostToPlotApplication;

/**
 * @extends EntityRepository<KnPostToPlotApplication>
 */
final class KnPostToPlotApplicationRepository extends EntityRepository implements KnPostToPlotApplicationRepositoryInterface
{
    #[\Override]
    public function prototype(): KnPostToPlotApplication
    {
        return new KnPostToPlotApplication();
    }

    #[\Override]
    public function save(KnPostToPlotApplication $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(KnPostToPlotApplication $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[\Override]
    public function getByPostAndPlot(int $postId, int $plotId): ?KnPostToPlotApplication
    {
        return $this->findOneBy(
            [
                'post_id' => $postId,
                'plot_id' => $plotId
            ]
        );
    }
}
