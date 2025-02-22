<?php

namespace platz1de\EasyEdit\command\defaults\clipboard;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\selection\ClipBoardManager;
use platz1de\EasyEdit\task\DynamicStoredRotateTask;
use pocketmine\player\Player;
use Throwable;

class RotateCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/rotate", "Rotate the Clipboard", [KnownPermissions::PERMISSION_CLIPBOARD]);
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		try {
			$selection = ClipBoardManager::getFromPlayer($player->getName());
		} catch (Throwable) {
			Messages::send($player, "no-clipboard");
			return;
		}

		DynamicStoredRotateTask::queue($player->getName(), $selection);
	}
}