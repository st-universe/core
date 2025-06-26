<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostToPlotApplication;

/**
 * @extends ObjectRepository<KnPostToPlotApplication>
 */
interface KnPostToPlotApplicationRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostToPlotApplication;

    public function save(KnPostToPlotApplication $post): void;

    public function delete(KnPostToPlotApplication $post): void;

    public function getByPostAndPlot(int $postId, int $plotId): ?KnPostToPlotApplication;
}
