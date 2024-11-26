<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class SettingsCoreTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    private SettingsCoreInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);
    }

    public function testGetPathExpectChildPathWhenNoParent(): void
    {
        $this->subject = new SettingsCore(null, 'child', $this->config);

        $result = $this->subject->getPath();

        $this->assertEquals('child', $result);
    }

    public function testGetPathExpectCombinedPathWhenParentSet(): void
    {
        $parent = $this->mock(SettingsInterface::class);

        $parent->shouldReceive('getPath')
            ->withNoArgs()
            ->once()
            ->andReturn('parent.path');

        $this->subject = new SettingsCore($parent, 'child', $this->config);

        $result = $this->subject->getPath();

        $this->assertEquals('parent.path.child', $result);
    }

    public function testGetConfigExpectConfigFromConstructor(): void
    {
        $this->subject = new SettingsCore(null, 'child', $this->config);

        $this->assertSame($this->config, $this->subject->getConfig());
    }

    public function testGetIntegerConfigValueExpectErrorWhenNotFound(): void
    {
        static::expectExceptionMessage('There is no corresponding config setting on path "path.setting"');
        static::expectException(StuConfigException::class);

        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $this->subject->getIntegerConfigValue('setting');
    }

    public function testGetIntegerConfigValueReturnValue(): void
    {
        $parent = $this->mock(SettingsInterface::class);


        $parent->shouldReceive('getPath')
            ->withNoArgs()
            ->once()
            ->andReturn('parent');

        $this->config->shouldReceive('get')
            ->with('parent.path.setting')
            ->once()
            ->andReturn(42);

        $this->subject = new SettingsCore($parent, 'path', $this->config);

        $result = $this->subject->getIntegerConfigValue('setting');

        $this->assertEquals(42, $result);
    }

    public function testGetIntegerConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $result = $this->subject->getIntegerConfigValue('setting', 5);

        $this->assertEquals(5, $result);
    }

    public function testGetIntegerConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "notAnInteger" with path "path.setting" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn('notAnInteger');

        $this->subject->getIntegerConfigValue('setting');
    }

    public function testGetStringConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "5" with path "path.setting" is no valid string.');
        static::expectException(StuConfigException::class);

        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(5);

        $this->subject->getStringConfigValue('setting');
    }

    public function testGetStringConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $result = $this->subject->getStringConfigValue('setting', 'default');

        $this->assertEquals('default', $result);
    }

    public function testGetStringConfigValueReturnValue(): void
    {
        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn('value');

        $result = $this->subject->getStringConfigValue('setting');

        $this->assertEquals('value', $result);
    }

    public function testGetBooleanConfigValueExpectErrorWhenValueTypeWrong(): void
    {
        static::expectExceptionMessage('The value "5" with path "path.setting" is no valid boolean.');
        static::expectException(StuConfigException::class);

        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(5);

        $this->subject->getBooleanConfigValue('setting');
    }

    public function testGetBooleanConfigValueReturnDefaultWhenDefaultSet(): void
    {
        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(null);

        $result = $this->subject->getBooleanConfigValue('setting', true);

        $this->assertTrue($result);
    }

    public function testGetBooleanConfigValueReturnValue(): void
    {
        $this->subject = new SettingsCore(null, 'path', $this->config);

        $this->config->shouldReceive('get')
            ->with('path.setting')
            ->once()
            ->andReturn(true);

        $result = $this->subject->getBooleanConfigValue('setting');

        $this->assertTrue($result);
    }
}
