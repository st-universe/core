<?php

namespace Stu\Orm\Entity;

interface LotteryTicketInterface
{
    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): LotteryTicketInterface;

    public function getPeriod(): string;

    public function setPeriod(string $period): LotteryTicketInterface;

    public function setIsWinner(bool $isWinner): bool;
}
