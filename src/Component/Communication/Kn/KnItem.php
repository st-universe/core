<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Doctrine\Common\Collections\Collection;
use JBBCode\Parser;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnItem implements KnItemInterface
{
    private ?int $mark = null;

    private bool $isHighlighted = false;

    public function __construct(
        private Parser $bbcodeParser,
        private KnCommentRepositoryInterface $knCommentRepository,
        private StatusBarFactoryInterface $statusBarFactory,
        private KnPostInterface $post,
        private UserInterface $currentUser
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->post->getId();
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->post->getUser();
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->post->getUserId();
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
    public function getDate(): int
    {
        return $this->post->getDate();
    }

    #[Override]
    public function getEditDate(): int
    {
        return $this->post->getEditDate();
    }

    #[Override]
    public function isEditAble(): bool
    {
        return $this->getDate() > time() - 600 && $this->post->getUser() === $this->currentUser;
    }

    #[Override]
    public function getPlot(): ?RpgPlotInterface
    {
        return $this->post->getRpgPlot();
    }

    #[Override]
    public function getCharacters(): Collection
    {
        return $this->post->getKnCharacters();
    }

    #[Override]
    public function getCommentCount(): int
    {
        return $this->knCommentRepository->getAmountByPost($this->post);
    }

    #[Override]
    public function displayContactLinks(): bool
    {
        $user = $this->post->getUser();
        return $user !== $this->currentUser && $user->getId() !== UserEnum::USER_NOONE;
    }

    #[Override]
    public function getUserName(): string
    {
        return $this->post->getUsername();
    }

    #[Override]
    public function isUserDeleted(): bool
    {
        return $this->post->getUserId() !== 1;
    }

    #[Override]
    public function isNewerThanMark(): bool
    {
        return $this->post->getId() > $this->currentUser->getKnMark();
    }

    #[Override]
    public function userCanRate(): bool
    {
        return !$this->userHasRated() && $this->currentUser !== $this->post->getUser() && $this->currentUser->getId() > 100;
    }

    #[Override]
    public function userHasRated(): bool
    {
        return array_key_exists($this->currentUser->getId(), $this->post->getRatings());
    }

    #[Override]
    public function getMark(): ?int
    {
        return $this->mark;
    }

    #[Override]
    public function setMark(int $mark): void
    {
        $this->mark = $mark;
    }

    #[Override]
    public function getDivClass(): string
    {
        return $this->isHighlighted ? 'red_box' : 'box';
    }

    #[Override]
    public function setIsHighlighted(bool $isHighlighted): void
    {
        $this->isHighlighted = $isHighlighted;
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
    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
