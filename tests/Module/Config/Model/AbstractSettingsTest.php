<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class AbstractSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|AbstractSettings */
    private AbstractSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);
    }

    public function testGetPathExpectChildPathWhenNoParent(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('child'));

        $method = $this->getMethod($this->subject, 'getPath');
        $path = $method->invokeArgs($this->subject, []);

        $this->assertEquals('child', $path);
    }

    public function testGetPathExpectCombinedPathWhenParentSet(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            ['parent', $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('child'));

        $method = $this->getMethod($this->subject, 'getPath');
        $path = $method->invokeArgs($this->subject, []);

        $this->assertEquals('parent.child', $path);
    }

    public function testGetConfigExpectConfigFromConstructor(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $method = $this->getMethod($this->subject, 'getConfig');
        $config = $method->invokeArgs($this->subject, []);

        $this->assertSame($this->config, $config);
    }

    public function testGetIntegerConfigValueExpectErrorWhenNotFound(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "path.setting"');
        static::expectException(StuConfigException::class);

        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $method = $this->getMethod($this->subject, 'getIntegerConfigValue');
        $method->invokeArgs($this->subject, ['setting']);
    }

    public function testGetIntegerConfigValueReturnValue(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(42);

        $method = $this->getMethod($this->subject, 'getIntegerConfigValue');
        $integerConfigValue = $method->invokeArgs($this->subject, ['setting']);

        $this->assertEquals(42, $integerConfigValue);
    }

    public function testGetIntegerConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $method = $this->getMethod($this->subject, 'getIntegerConfigValue');
        $integerConfigValue = $method->invokeArgs($this->subject, ['setting', 5]);

        $this->assertEquals(5, $integerConfigValue);
    }

    public function testGetIntegerConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "notAnInteger" with path "path.setting" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn('notAnInteger');

        $method = $this->getMethod($this->subject, 'getIntegerConfigValue');
        $method->invokeArgs($this->subject, ['setting']);
    }

    public function testGetStringConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "5" with path "path.setting" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(5);

        $method = $this->getMethod($this->subject, 'getStringConfigValue');
        $method->invokeArgs($this->subject, ['setting']);
    }

    public function testGetStringConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $method = $this->getMethod($this->subject, 'getStringConfigValue');
        $stringConfigValue = $method->invokeArgs($this->subject, ['setting', 'default']);

        $this->assertEquals('default', $stringConfigValue);
    }

    public function testGetStringConfigValueReturnValue(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn('value');

        $method = $this->getMethod($this->subject, 'getStringConfigValue');
        $stringConfigValue = $method->invokeArgs($this->subject, ['setting']);

        $this->assertEquals('value', $stringConfigValue);
    }

    public function testGetBooleanConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "5" with path "path.setting" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(5);

        $method = $this->getMethod($this->subject, 'getBooleanConfigValue');
        $method->invokeArgs($this->subject, ['setting']);
    }

    public function testGetBooleanConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $method = $this->getMethod($this->subject, 'getBooleanConfigValue');
        $booleanConfigValue = $method->invokeArgs($this->subject, ['setting', true]);

        $this->assertTrue($booleanConfigValue);
    }

    public function testGetBooleanConfigValueReturnValue(): void
    {
        $this->subject = $this->getMockForAbstractClass(
            AbstractSettings::class,
            [null, $this->config]
        );

        $this->subject->expects($this->any())
            ->method('getConfigPath')
            ->will($this->returnValue('path'));

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(true);

        $method = $this->getMethod($this->subject, 'getBooleanConfigValue');
        $booleanConfigValue = $method->invokeArgs($this->subject, ['setting']);

        $this->assertTrue($booleanConfigValue);
    }
}
