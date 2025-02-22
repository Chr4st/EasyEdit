<?php

namespace platz1de\EasyEdit\task;

use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\schematic\BlockConvertor;
use platz1de\EasyEdit\selection\DynamicBlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionContext;
use platz1de\EasyEdit\thread\input\TaskInputData;
use platz1de\EasyEdit\thread\modules\StorageModule;
use platz1de\EasyEdit\thread\output\MessageSendData;
use platz1de\EasyEdit\utils\ExtendedBinaryStream;
use platz1de\EasyEdit\utils\MixedUtils;
use platz1de\EasyEdit\utils\TileUtils;
use pocketmine\math\Axis;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use UnexpectedValueException;

class DynamicStoredFlipTask extends ExecutableTask
{
	private int $saveId;
	private int $axis;

	/**
	 * @param string $owner
	 * @param int    $saveId
	 * @param int    $axis
	 * @return DynamicStoredFlipTask
	 */
	public static function from(string $owner, int $saveId, int $axis): DynamicStoredFlipTask
	{
		$instance = new self($owner);
		$instance->saveId = $saveId;
		$instance->axis = $axis;
		return $instance;
	}

	/**
	 * @param string $owner
	 * @param int    $id
	 * @param int    $axis
	 */
	public static function queue(string $owner, int $id, int $axis): void
	{
		TaskInputData::fromTask(self::from($owner, $id, $axis));
	}

	/**
	 * @return string
	 */
	public function getTaskName(): string
	{
		return "dynamic_storage_flip";
	}

	public function execute(): void
	{
		$start = microtime(true);
		$selection = StorageModule::getStored($this->saveId);
		if (!$selection instanceof DynamicBlockListSelection) {
			throw new UnexpectedValueException("Storage at id " . $this->saveId . " contained " . get_class($selection) . " expected " . DynamicBlockListSelection::class);
		}
		$flipped = new DynamicBlockListSelection($selection->getPlayer());
		$flipped->setPos1(new Vector3(0, World::Y_MIN, 0));
		$flipped->setPos2(new Vector3($selection->getPos2()->getX(), $selection->getPos2()->getY(), $selection->getPos2()->getZ()));
		$flipped->getManager()->load($flipped->getPos1(), $flipped->getPos2());
		switch ($this->axis) {
			case Axis::X:
				$flipped->setPoint(new Vector3($selection->getPos2()->getX() - $selection->getPoint()->getX(), $selection->getPoint()->getY(), $selection->getPoint()->getZ()));
				$selection->useOnBlocks(new Vector3(0, 0, 0), function (int $x, int $y, int $z) use ($selection, $flipped): void {
					$block = $selection->getIterator()->getBlockAt($x, $y, $z);
					Selection::processBlock($block);
					$flipped->addBlock($selection->getPos2()->getFloorX() - $x, $y, $z, BlockConvertor::flip(Axis::X, $block));
				}, SelectionContext::full(), $selection);
				foreach ($selection->getTiles() as $tile) {
					$flipped->addTile(TileUtils::flipCompound(Axis::X, $tile, $selection->getPos2()->getFloorX()));
				}
				break;
			case Axis::Y:
				$flipped->setPoint(new Vector3($selection->getPoint()->getX(), $selection->getPos2()->getY() - $selection->getPoint()->getY(), $selection->getPoint()->getZ()));
				$selection->useOnBlocks(new Vector3(0, 0, 0), function (int $x, int $y, int $z) use ($selection, $flipped): void {
					$block = $selection->getIterator()->getBlockAt($x, $y, $z);
					Selection::processBlock($block);
					$flipped->addBlock($x, $selection->getPos2()->getFloorY() - $y, $z, BlockConvertor::flip(Axis::Y, $block));
				}, SelectionContext::full(), $selection);
				foreach ($selection->getTiles() as $tile) {
					$flipped->addTile(TileUtils::flipCompound(Axis::Y, $tile, $selection->getPos2()->getFloorY()));
				}
				break;
			case Axis::Z:
				$flipped->setPoint(new Vector3($selection->getPoint()->getX(), $selection->getPoint()->getY(), $selection->getPos2()->getZ() - $selection->getPoint()->getZ()));
				$selection->useOnBlocks(new Vector3(0, 0, 0), function (int $x, int $y, int $z) use ($selection, $flipped): void {
					$block = $selection->getIterator()->getBlockAt($x, $y, $z);
					Selection::processBlock($block);
					$flipped->addBlock($x, $y, $selection->getPos2()->getFloorZ() - $z, BlockConvertor::flip(Axis::Z, $block));
				}, SelectionContext::full(), $selection);
				foreach ($selection->getTiles() as $tile) {
					$flipped->addTile(TileUtils::flipCompound(Axis::Z, $tile, $selection->getPos2()->getFloorZ()));
				}
				break;
			default:
				throw new UnexpectedValueException("Invalid axis " . $this->axis);
		}
		StorageModule::forceStore($this->saveId, $flipped);
		MessageSendData::from($this->getOwner(), Messages::replace("blocks-flipped", ["{time}" => (string) round(microtime(true) - $start, 2), "{changed}" => MixedUtils::humanReadable($flipped->getIterator()->getWrittenBlockCount())]));
	}

	public function putData(ExtendedBinaryStream $stream): void
	{
		$stream->putInt($this->saveId);
		$stream->putInt($this->axis);
	}

	public function parseData(ExtendedBinaryStream $stream): void
	{
		$this->saveId = $stream->getInt();
		$this->axis = $stream->getInt();
	}
}