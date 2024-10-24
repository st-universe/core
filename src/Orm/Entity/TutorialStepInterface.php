<?php

namespace Stu\Orm\Entity;

interface TutorialStepInterface
{
    public function getId(): int;

    /** @return array{elementIds: array<string>, title: string, text: string} */
    public function getPayload(): array;

    public function getPreviousStep(): ?TutorialStepInterface;

    /** @return array<int> */
    public function getNextStepIds(): array;
}
