<?php

namespace Stu\Lib\Interaction;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Interaction\Member\InteractionMemberInterface;

class CustomizedInteractionChecker implements CustomizedInteractionCheckerInterface
{
    private InteractionMemberInterface $source;
    private InteractionMemberInterface $target;

    /** @var array<InteractionCheckType> */
    private array $checkTypes;

    public function setSource(InteractionMemberInterface $source): void
    {
        $this->source = $source;
    }

    public function setTarget(InteractionMemberInterface $target): void
    {
        $this->target = $target;
    }

    /** @param array<InteractionCheckType> $checkTypes */
    public function setCheckTypes(array $checkTypes): void
    {
        $this->checkTypes = $checkTypes;
    }

    #[\Override]
    public function check(InformationInterface $information): bool
    {
        $targetUser = $this->target->getUser();
        if (
            $this->shouldCheck(InteractionCheckType::EXPECT_TARGET_NO_VACATION)
            && $targetUser !== null
            && $targetUser->isVacationRequestOldEnough()
        ) {
            $information->addInformation(InteractionCheckType::EXPECT_TARGET_NO_VACATION->getReason());
            return false;
        }

        if ($this->source->getLocation() !== $this->target->getLocation()) {
            return false;
        }

        $refused = $this->source->canAccess(
            $this->target,
            fn (InteractionCheckType $checkType): bool => $this->shouldCheck($checkType)
        );
        if ($refused !== null) {
            $information->addInformation($refused->getReason());
            return false;
        }

        $refused = $this->target->canBeAccessedFrom(
            $this->source,
            fn (InteractionCheckType $checkType): bool => $this->shouldCheck($checkType)
        );
        if ($refused !== null) {
            $information->addInformation($refused->getReason());
            return false;
        }

        return true;
    }

    private function shouldCheck(InteractionCheckType $checkType): bool
    {
        return in_array($checkType, $this->checkTypes);
    }
}
