<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostToPlotApplicationInterface;

interface KnPostToPlotApplicationRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostToPlotApplicationInterface;

    public function save(KnPostToPlotApplicationInterface $post): void;

    public function delete(KnPostToPlotApplicationInterface $post): void;

    public function getByPostAndPlot(int $postId, int $plotId): ?KnPostToPlotApplicationInterface;
}
