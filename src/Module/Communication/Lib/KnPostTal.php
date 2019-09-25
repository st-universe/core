<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class KnPostTal implements KnPostTalInterface
{
    private $knCommentRepository;

    private $post;

    private $currentUser;

    public function __construct(
        KnCommentRepositoryInterface $knCommentRepository,
        KnPostInterface $post,
        UserInterface $currentUser
    ) {
        $this->post = $post;
        $this->currentUser = $currentUser;
        $this->knCommentRepository = $knCommentRepository;
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

    public function getTitle(): string
    {
        return $this->post->getTitle();
    }

    public function getText(): string
    {
        return $this->post->getText();
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
        return $this->getDate() > time() - 600 && $this->getUserId() === $this->currentUser->getId();
    }

    public function getPlotId(): ?int
    {
        return $this->post->getPlotId();
    }

    public function getRPGPlot(): ?RpgPlotInterface
    {
        return $this->post->getRpgPlot();
    }

    public function getCommentCount(): int
    {
        return $this->knCommentRepository->getAmountByPost((int)$this->getId());
    }

    public function displayUserLinks(): bool
    {
        return $this->getUserId() > 0 && $this->getUserId() !== $this->currentUser->getId();
    }

    public function getUserName(): string
    {
        return $this->post->getUserName();
    }

    public function isNewerThanMark(): bool
    {
        return $this->getId() > $this->currentUser->getKNMark();
    }
}