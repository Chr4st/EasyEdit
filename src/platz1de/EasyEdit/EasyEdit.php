<?php

namespace platz1de\EasyEdit;

use platz1de\EasyEdit\command\CommandManager;
use platz1de\EasyEdit\command\defaults\clipboard\CopyCommand;
use platz1de\EasyEdit\command\defaults\clipboard\CutCommand;
use platz1de\EasyEdit\command\defaults\clipboard\FlipCommand;
use platz1de\EasyEdit\command\defaults\clipboard\InsertCommand;
use platz1de\EasyEdit\command\defaults\clipboard\LoadSchematicCommand;
use platz1de\EasyEdit\command\defaults\clipboard\PasteCommand;
use platz1de\EasyEdit\command\defaults\clipboard\RotateCommand;
use platz1de\EasyEdit\command\defaults\clipboard\SaveSchematicCommand;
use platz1de\EasyEdit\command\defaults\generation\CylinderCommand;
use platz1de\EasyEdit\command\defaults\generation\HollowCylinderCommand;
use platz1de\EasyEdit\command\defaults\generation\HollowSphereCommand;
use platz1de\EasyEdit\command\defaults\generation\NoiseCommand;
use platz1de\EasyEdit\command\defaults\generation\SphereCommand;
use platz1de\EasyEdit\command\defaults\history\RedoCommand;
use platz1de\EasyEdit\command\defaults\history\UndoCommand;
use platz1de\EasyEdit\command\defaults\selection\CenterCommand;
use platz1de\EasyEdit\command\defaults\selection\CountCommand;
use platz1de\EasyEdit\command\defaults\selection\ExtendCommand;
use platz1de\EasyEdit\command\defaults\selection\ExtinguishCommand;
use platz1de\EasyEdit\command\defaults\selection\FirstPositionCommand;
use platz1de\EasyEdit\command\defaults\selection\MoveCommand;
use platz1de\EasyEdit\command\defaults\selection\NaturalizeCommand;
use platz1de\EasyEdit\command\defaults\selection\ReplaceCommand;
use platz1de\EasyEdit\command\defaults\selection\SecondPositionCommand;
use platz1de\EasyEdit\command\defaults\selection\SetCommand;
use platz1de\EasyEdit\command\defaults\selection\SidesCommand;
use platz1de\EasyEdit\command\defaults\selection\SmoothCommand;
use platz1de\EasyEdit\command\defaults\selection\StackCommand;
use platz1de\EasyEdit\command\defaults\selection\StackInsertCommand;
use platz1de\EasyEdit\command\defaults\selection\WallCommand;
use platz1de\EasyEdit\command\defaults\utility\BenchmarkCommand;
use platz1de\EasyEdit\command\defaults\utility\BlockInfoCommand;
use platz1de\EasyEdit\command\defaults\utility\BrushCommand;
use platz1de\EasyEdit\command\defaults\utility\CancelCommand;
use platz1de\EasyEdit\command\defaults\utility\StatusCommand;
use platz1de\EasyEdit\thread\EditAdapter;
use platz1de\EasyEdit\thread\EditThread;
use platz1de\EasyEdit\utils\CompoundTile;
use platz1de\EasyEdit\utils\ConfigManager;
use pocketmine\block\tile\TileFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;

class EasyEdit extends PluginBase
{
	private static EasyEdit $instance;

	public function onEnable(): void
	{
		self::$instance = $this;

		if (!is_dir(self::getSchematicPath()) && !mkdir(self::getSchematicPath()) && !is_dir(self::getSchematicPath())) {
			throw new AssumptionFailedError("Failed to created schematic directory");
		}

		$thread = new EditThread(Server::getInstance()->getLogger());
		$thread->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);

		Messages::load();
		ConfigManager::load();

		$this->getScheduler()->scheduleRepeatingTask(new EditAdapter(), 1);

		Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);

		CommandManager::registerCommands([
			//Selection
			new FirstPositionCommand(),
			new SecondPositionCommand(),
			new ExtendCommand(),
			new SetCommand(),
			new ReplaceCommand(),
			new NaturalizeCommand(),
			new SmoothCommand(),
			new CenterCommand(),
			new WallCommand(),
			new SidesCommand(),
			new MoveCommand(),
			new StackCommand(),
			new StackInsertCommand(),
			new CountCommand(),
			new ExtinguishCommand(),

			//History
			new UndoCommand(),
			new RedoCommand(),

			//Clipboard
			new CopyCommand(),
			new CutCommand(),
			new PasteCommand(),
			new InsertCommand(),
			new RotateCommand(),
			new FlipCommand(),
			new LoadSchematicCommand(),
			new SaveSchematicCommand(),

			//Generation
			new SphereCommand(),
			new HollowSphereCommand(),
			new CylinderCommand(),
			new HollowCylinderCommand(),
			new NoiseCommand(),

			//Utility
			new BrushCommand(),
			new BlockInfoCommand(),
			new StatusCommand(),
			new CancelCommand(),
			new BenchmarkCommand(),
		]);

		//Just for sending block data without using the protocol directly
		TileFactory::getInstance()->register(CompoundTile::class);
	}

	/**
	 * @return EasyEdit
	 */
	public static function getInstance(): self
	{
		return self::$instance;
	}

	/**
	 * @return string
	 */
	public static function getSchematicPath(): string
	{
		return self::getInstance()->getDataFolder() . "schematics" . DIRECTORY_SEPARATOR;
	}
}