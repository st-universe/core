<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Override;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentArchivRepositoryInterface;

final class KnArchiveItem implements KnArchiveItemInterface
{
    private ?RpgPlotArchiv $plot = null;

    public function __construct(
        private KnBbCodeParser $bbcodeParser,
        private StatusBarFactoryInterface $statusBarFactory,
        private KnPostArchiv $post,
        private KnCommentArchivRepositoryInterface $knCommentArchivRepository
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->post->getId();
    }

    #[Override]
    public function getFormerId(): int
    {
        return $this->post->getFormerId();
    }

    #[Override]
    public function getTitle(): ?string
    {
        return $this->post->getTitle();
    }

    #[Override]
    public function getText(): string
    {
        return $this->bbcodeParser->parse($this->post->getText())->getAsHTML();
    }

    #[Override]
    public function getUsername(): string
    {
        $username = $this->post->getUsername();
        $userId = $this->post->getdelUserId() ?? $this->post->getUserId();

        return sprintf('%s (%d)', $username, $userId);
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->post->getUserId() ?? 0;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->post->getDate();
    }

    #[Override]
    public function getEditDate(): ?int
    {
        return $this->post->getEditDate();
    }

    #[Override]
    public function getRpgPlot(): ?RpgPlotArchiv
    {
        return $this->plot;
    }

    #[Override]
    public function setPlot(?RpgPlotArchiv $plot): void
    {
        $this->plot = $plot;
    }

    #[Override]
    public function getVersion(): ?string
    {
        return $this->post->getVersion();
    }

    #[Override]
    public function getPost(): KnPostArchiv
    {
        return $this->post;
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function getRatings(): array
    {
        return $this->post->getRatings();
    }

    #[Override]
    public function getRatingBar(): string
    {
        $ratingAmount = count($this->post->getRatings());

        if ($ratingAmount === 0) {
            return '';
        }

        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor(StatusBarColorEnum::STATUSBAR_YELLOW)
            ->setLabel('Bewertung')
            ->setMaxValue($ratingAmount)
            ->setValue($this->getRating())
            ->render();
    }

    #[Override]
    public function getRating(): int
    {
        return (int) array_sum(
            array_filter(
                $this->post->getRatings(),
                static fn(int $value): bool => $value > 0
            )
        );
    }

    #[Override]
    public function userCanRate(): bool
    {
        return false;
    }

    #[Override]
    public function getCommentCount(): int
    {
        return $this->knCommentArchivRepository->getAmountByFormerId($this->post->getFormerId());
    }

    #[Override]
    public function getDivClass(): string
    {
        return 'box kn_archive_post';
    }

    /**
     * @return array<int>|null
     */
    #[Override]
    public function getRefs(): ?array
    {
        return $this->post->getRefs();
    }
}
