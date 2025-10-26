<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentArchivRepositoryInterface;

final class KnArchiveFactory implements KnArchiveFactoryInterface
{
    public function __construct(
        private KnBbCodeParser $bbcodeParser,
        private StatusBarFactoryInterface $statusBarFactory,
        private KnCommentArchivRepositoryInterface $knCommentArchivRepository
    ) {}

    #[\Override]
    public function createKnArchiveItem(KnPostArchiv $post, User $user, ?RpgPlotArchiv $plot = null): KnArchiveItemInterface
    {
        $item = new KnArchiveItem(
            $this->bbcodeParser,
            $this->statusBarFactory,
            $post,
            $this->knCommentArchivRepository
        );
        if ($plot !== null) {
            $item->setPlot($plot);
        }
        return $item;
    }
}
