<?php

namespace platz1de\EasyEdit\command\defaults\selection;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\pattern\logic\relation\BlockPattern;
use platz1de\EasyEdit\pattern\parser\ParseError;
use platz1de\EasyEdit\pattern\parser\PatternParser;
use platz1de\EasyEdit\pattern\PatternArgumentData;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionManager;
use platz1de\EasyEdit\task\editing\selection\pattern\SetTask;
use pocketmine\player\Player;
use Throwable;

class ReplaceCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/replace", "Replace the selected Area", [KnownPermissions::PERMISSION_EDIT], "//replace <block> <pattern>");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		if (($args[1] ?? "") === "") {
			$player->sendMessage($this->getUsage());
			return;
		}

		try {
			$block = PatternParser::getBlockType($args[0]);
			$pattern = PatternParser::parseInputCombined($args, 1, $player);
		} catch (ParseError $exception) {
			$player->sendMessage($exception->getMessage());
			return;
		}

		try {
			$selection = SelectionManager::getFromPlayer($player->getName());
			Selection::validate($selection);
		} catch (Throwable) {
			Messages::send($player, "no-selection");
			return;
		}

		SetTask::queue($selection, BlockPattern::from([$pattern], PatternArgumentData::create()->setBlock($block)), $player->getPosition());
	}
}