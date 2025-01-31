<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditInfluenceArea;

use Override;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class EditInfluenceArea implements ActionControllerInterface
{
	public const string ACTION_IDENTIFIER = 'B_EDIT_INFLUENCE_AREA';

	public function __construct(private EditInfluenceAreaRequestInterface $editInfluenceAreaRequest, private StarSystemRepositoryInterface $starSystemRepository, private MapRepositoryInterface $mapRepository) {}

	#[Override]
	public function handle(GameControllerInterface $game): void
	{
		$selectedField = $this->mapRepository->find($this->editInfluenceAreaRequest->getFieldId());

		if ($selectedField === null) {
			return;
		}

		if ($this->editInfluenceAreaRequest->getInfluenceAreaId() == 9999) {

			$selectedField->setInfluenceArea(null);
		} else {

			$system = $this->starSystemRepository->find($this->editInfluenceAreaRequest->getInfluenceAreaId());
			if ($system === null) {
				return;
			}

			$selectedField->setInfluenceArea($system);
		}

		$this->mapRepository->save($selectedField);

		$game->setView(Noop::VIEW_IDENTIFIER);
	}

	#[Override]
	public function performSessionCheck(): bool
	{
		return false;
	}
}