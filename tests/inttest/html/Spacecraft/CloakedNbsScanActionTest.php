<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Stu\TwigTestCase;
use Twig\Environment;

class CloakedNbsScanActionTest extends TwigTestCase
{
    public function testCloakedUnwarpedShipCanOpenLimitedScanFromNbs(): void
    {
        $source = new class {
            public function getId(): int
            {
                return 42;
            }

            public function displayNbsActions(): bool
            {
                return false;
            }

            public function getWarpDriveState(): bool
            {
                return false;
            }

            public function isCloaked(): bool
            {
                return true;
            }

            public function canIntercept(): bool
            {
                return true;
            }
        };

        $wrapper = new class($source) {
            public function __construct(private readonly object $source) {}

            public function get(): object
            {
                return $this->source;
            }
        };

        $target = new class {
            public function getId(): int
            {
                return 43;
            }

            public function getName(): string
            {
                return 'TARGET';
            }

            public function isScanPossible(): bool
            {
                return true;
            }

            public function isInterceptable(): bool
            {
                return true;
            }

            public function getHoldingWebBackgroundStyle(): string
            {
                return '';
            }

            public function isSelectable(): bool
            {
                return false;
            }

            public function getRump(): object
            {
                return new class {
                    public function get3DModel(): ?string
                    {
                        return null;
                    }
                };
            }

            public function isCloaked(): bool
            {
                return false;
            }

            public function getRumpId(): int
            {
                return 6501;
            }

            public function getRumpName(): string
            {
                return 'RUMP';
            }

            public function hasLogBook(): bool
            {
                return false;
            }

            public function getRPGModuleState(): bool
            {
                return false;
            }

            public function isTrumfield(): bool
            {
                return false;
            }

            public function getHull(): int
            {
                return 10;
            }

            public function getMaxHull(): int
            {
                return 20;
            }

            public function isShielded(): bool
            {
                return false;
            }

            public function isContactable(): bool
            {
                return true;
            }

            public function getUserName(): string
            {
                return 'USER';
            }

            public function getUserId(): int
            {
                return 100;
            }
        };

        $template = $this->getContainer()->get(Environment::class)->createTemplate(
            "{% from 'html/shipmacros.twig' import nbslist_body %}<tr>{{ nbslist_body(WRAPPER, NBS_ITEM, false) }}</tr>"
        );

        $html = $template->render([
            'WRAPPER' => $wrapper,
            'NBS_ITEM' => $target,
        ]);

        $this->assertStringContainsString('showScanWindow(this, 42,43)', $html);
        $this->assertStringContainsString('B_INTERCEPT=1&id=42&target=43', $html);
        $this->assertStringContainsString('Tarnung aktiv', $html);
        $this->assertStringNotContainsString('Warpantrieb aktiv', $html);
    }
}
