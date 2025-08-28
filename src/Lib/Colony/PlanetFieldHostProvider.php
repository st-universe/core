<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use Override;
use request;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

class PlanetFieldHostProvider implements PlanetFieldHostProviderInterface
{
    public function __construct(private ColonySandboxRepositoryInterface $colonySandboxRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private ColonyLoaderInterface $colonyLoader) {}

    #[Override]
    public function loadFieldViaRequestParameter(User $user, bool $checkForEntityLock = true): PlanetField
    {
        if (!request::has('fid')) {
            throw new RuntimeException('request param "fid" is missing');
        }

        $fid = request::indInt('fid');
        $field = $this->planetFieldRepository->find($fid);
        if ($field === null) {
            throw new RuntimeException(sprintf('planetField with following id does not exist: %s', $fid));
        }

        $host = $field->getHost();
        $this->getHostInternal($host->getId(), $host->getHostType(), $user, $checkForEntityLock);

        return $field;
    }

    #[Override]
    public function loadHostViaRequestParameters(User $user, bool $checkForEntityLock = true): PlanetFieldHostInterface
    {
        if (!request::has('id')) {
            throw new RuntimeException('request param "id" is missing');
        }
        if (!request::has('hosttype')) {
            throw new RuntimeException('request param "hosttype" is missing');
        }

        $id = request::indInt('id');
        $hostType = PlanetFieldHostTypeEnum::from(request::indInt('hosttype'));

        return $this->getHostInternal($id, $hostType, $user, $checkForEntityLock);
    }

    private function getHostInternal(
        int $id,
        PlanetFieldHostTypeEnum $hostType,
        User $user,
        bool $checkForEntityLock
    ): PlanetFieldHostInterface {

        if ($hostType === PlanetFieldHostTypeEnum::COLONY) {
            return $this->colonyLoader->loadWithOwnerValidation(
                $id,
                $user->getId(),
                $checkForEntityLock
            );
        }
        $sandbox = $this->colonySandboxRepository->find($id);
        if ($sandbox === null) {
            throw new RuntimeException(sprintf('sandbox with following id does not exist: %d', $id));
        }

        if ($sandbox->getUser()->getId() !== $user->getId()) {
            throw new SanityCheckException('sandbox does belong to other user');
        }

        return $sandbox;
    }
}
