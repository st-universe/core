<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\StuTestCase;

class StuHashTest extends StuTestCase
{
    private StuHashInterface $stuHash;

    private MockInterface $config;

    #[\Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->stuHash = new StuHash($this->config);
    }

    public function testHash(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.hash_method')
            ->andReturn('sha1');

        $hash = $this->stuHash->hash('foo');

        $this->assertEquals(sha1('foo'), $hash);
    }
}
