<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Override;
use RuntimeException;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\StuTestCase;

class TransferStrategyProviderTest extends StuTestCase
{
    /** @var MockInterface|TransferStrategyInterface */
    private $transferStrategy;

    private TransferStrategyProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->transferStrategy = $this->mock(TransferStrategyInterface::class);

        $this->subject = new TransferStrategyProvider([TransferTypeEnum::CREW->value => $this->transferStrategy]);
    }

    public function testGetTransferStrategyExpectRuntimExceptionWhenTypeUnknown(): void
    {
        static::expectExceptionMessage('transfer strategy with typeValue 1 does not exist');
        static::expectException(RuntimeException::class);

        $this->subject->getTransferStrategy(TransferTypeEnum::COMMODITIES);
    }

    public function testGetTransferStrategyExpectStrategyWhenTypeRegistered(): void
    {
        $result = $this->subject->getTransferStrategy(TransferTypeEnum::CREW);

        $this->assertTrue($result instanceof TransferStrategyInterface);
    }
}
