<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use JBBCode\Parser;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnItem implements KnItemInterface
{
    private Parser $bbcodeParser;

    private KnCommentRepositoryInterface $knCommentRepository;

    private KnPostInterface $post;

    private UserInterface $currentUser;

    private ?int $mark = null;

    private bool $isHighlighted = false;

    public function __construct(
        Parser $bbcodeParser,
        KnCommentRepositoryInterface $knCommentRepository,
        KnPostInterface $post,
        UserInterface $currentUser
    ) {
        $this->bbcodeParser = $bbcodeParser;
        $this->knCommentRepository = $knCommentRepository;
        $this->post = $post;
        $this->currentUser = $currentUser;
    }

    public function getId(): int
    {
        return $this->post->getId();
    }

    public function getUser(): ?UserInterface
    {
        return $this->post->getUser();
    }

    public function getUserId(): int
    {
        return $this->post->getUserId();
    }

    public function getTitle(): ?string
    {
        return $this->post->getTitle();
    }

    public function getText(): string
    {
        return $this->bbcodeParser->parse($this->post->getText())->getAsHTML();
    }

    public function getDate(): int
    {
        return $this->post->getDate();
    }

    public function getEditDate(): int
    {
        return $this->post->getEditDate();
    }

    public function isEditAble(): bool
    {
        return $this->getDate() > time() - 600 && $this->post->getUser() === $this->currentUser;
    }

    public function getPlot(): ?RpgPlotInterface
    {
        return $this->post->getRpgPlot();
    }

    public function getCommentCount(): int
    {
        return $this->knCommentRepository->getAmountByPost($this->post);
    }

    public function displayContactLinks(): bool
    {
        $user = $this->post->getUser();
        return $user !== $this->currentUser && $user->getId() !== UserEnum::USER_NOONE;
    }

    public function getUserName(): string
    {
        return $this->post->getUsername();
    }

    public function isUserDeleted(): bool
    {
        return $this->post->getUserId() !== 1;
    }

    public function isNewerThanMark(): bool
    {
        return $this->post->getId() > $this->currentUser->getKnMark();
    }

    public function userCanRate(): bool
    {
        return !$this->userHasRated() && $this->currentUser !== $this->post->getUser() && $this->currentUser->getId() > 100;
    }

    public function userHasRated(): bool
    {
        return array_key_exists($this->currentUser->getId(), $this->post->getRatings());
    }

    public function getMark(): ?int
    {
        return $this->mark;
    }

    public function setMark(int $mark): void
    {
        $this->mark = $mark;
    }

    public function getDivClass(): string
    {
        return $this->isHighlighted ? 'red_box' : 'box';
    }

    public function setIsHighlighted(bool $isHighlighted): void
    {
        $this->isHighlighted = $isHighlighted;
    }

    public function getRating(): int
    {
        return (int) array_sum(
            array_filter(
                $this->post->getRatings(),
                static fn (int $value): bool => $value > 0
            )
        );
    }

    public function getRatingBar(): string
    {
        $ratingAmount = count($this->post->getRatings());

        if ($ratingAmount === 0) {
            return '';
        }

        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_YELLOW)
            ->setLabel(_('Bewertung'))
            ->setMaxValue($ratingAmount)
            ->setValue($this->getRating())
            ->render();
    }
    public function hasTranslation(): bool
    {
        $text = $this->getText();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
