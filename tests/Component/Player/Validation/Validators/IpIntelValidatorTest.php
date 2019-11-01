<?php

namespace Stu\Component\Player\Validation\Validators;

use DateTimeInterface;
use Mockery;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\PlayerTagTypeEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserIpTableInterface;
use Stu\Orm\Entity\UserTagInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserTagRepositoryInterface;
use Stu\StuTestCase;
use Usox\IpIntel\Exception\ServiceException;
use Usox\IpIntel\IpIntelInterface;

class IpIntelValidatorTest extends StuTestCase
{

    /**
     * @var null|MockInterface|IpIntelInterface
     */
    private $ipIntel;

    /**
     * @var null|MockInterface|UserIpTableRepositoryInterface
     */
    private $userIpTableRepo;

    /**
     * @var null|MockInterface|UserTagRepositoryInterface
     */
    private $userTagRepo;

    /**
     * @var null|MockInterface|ConfigInterface
     */
    private $config;

    /**
     * @var IpIntelValidator|null
     */
    private $validator;

    public function setUp(): void
    {
        $this->ipIntel = $this->mock(IpIntelInterface::class);
        $this->userIpTableRepo = $this->mock(UserIpTableRepositoryInterface::class);
        $this->userTagRepo = $this->mock(UserTagRepositoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->validator = new IpIntelValidator(
            $this->ipIntel,
            $this->userIpTableRepo,
            $this->userTagRepo,
            $this->config
        );
    }

    public function testValidateReturnsTrueIfValidationIsTurnedOff(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_propability')
            ->once()
            ->andReturn(0);

        $this->assertTrue(
            $this->validator->validate($user)
        );
    }

    public function testValidateReturnsTrueOnMissingRecord(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->userIpTableRepo->shouldReceive('findMostRecentByUser')
            ->with($user)
            ->once()
            ->andReturnNull();

        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_propability')
            ->once()
            ->andReturn(42);

        $this->assertTrue(
            $this->validator->validate($user)
        );
    }

    public function testValidateReturnsTrueOnIpIntelException(): void
    {
        $user = $this->mock(UserInterface::class);
        $ipTableEntry = $this->mock(UserIpTableInterface::class);

        $propability = 1;
        $score = 666;
        $ip = '1.2.3.4';

        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_propability')
            ->once()
            ->andReturn($propability);
        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_score')
            ->once()
            ->andReturn($score);

        $ipTableEntry->shouldReceive('getIp')
            ->withNoArgs()
            ->once()
            ->andReturn($ip);

        $this->userIpTableRepo->shouldReceive('findMostRecentByUser')
            ->with($user)
            ->once()
            ->andReturn($ipTableEntry);

        $this->ipIntel->shouldReceive('validate')
            ->with($ip, $score)
            ->once()
            ->andThrow(new ServiceException());

        $this->assertTrue(
            $this->validator->validate($user)
        );
    }

    public function testValidateTagsUserAndReturnsTrue(): void
    {
        $user = $this->mock(UserInterface::class);
        $ipTableEntry = $this->mock(UserIpTableInterface::class);
        $tag = $this->mock(UserTagInterface::class);

        $propability = 1;
        $score = 666;
        $ip = '1.2.3.4';

        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_propability')
            ->once()
            ->andReturn($propability);
        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_score')
            ->once()
            ->andReturn($score);

        $ipTableEntry->shouldReceive('getIp')
            ->withNoArgs()
            ->once()
            ->andReturn($ip);

        $this->userIpTableRepo->shouldReceive('findMostRecentByUser')
            ->with($user)
            ->once()
            ->andReturn($ipTableEntry);

        $this->ipIntel->shouldReceive('validate')
            ->with($ip, $score)
            ->once()
            ->andReturnFalse();

        $this->userTagRepo->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($tag);
        $this->userTagRepo->shouldReceive('save')
            ->with($tag)
            ->once();

        $tag->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $tag->shouldReceive('setDate')
            ->with(Mockery::type(DateTimeInterface::class))
            ->once()
            ->andReturnSelf();
        $tag->shouldReceive('setTagTypeId')
            ->with(PlayerTagTypeEnum::FRAUD)
            ->once()
            ->andReturnSelf();

        $this->assertTrue(
            $this->validator->validate($user)
        );
    }

    public function testValidateValidatesAndReturnsTrue(): void
    {
        $user = $this->mock(UserInterface::class);
        $ipTableEntry = $this->mock(UserIpTableInterface::class);

        $propability = 1;
        $score = 666;
        $ip = '1.2.3.4';

        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_propability')
            ->once()
            ->andReturn($propability);
        $this->config->shouldReceive('get')
            ->with('security.validation.ip_intel_validation_score')
            ->once()
            ->andReturn($score);

        $ipTableEntry->shouldReceive('getIp')
            ->withNoArgs()
            ->once()
            ->andReturn($ip);

        $this->userIpTableRepo->shouldReceive('findMostRecentByUser')
            ->with($user)
            ->once()
            ->andReturn($ipTableEntry);

        $this->ipIntel->shouldReceive('validate')
            ->with($ip, $score)
            ->once()
            ->andReturnTrue();


        $this->assertTrue(
            $this->validator->validate($user)
        );
    }
}
