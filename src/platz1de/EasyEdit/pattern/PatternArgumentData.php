<?php

namespace platz1de\EasyEdit\pattern;

use platz1de\EasyEdit\pattern\block\StaticBlock;
use platz1de\EasyEdit\pattern\parser\PatternParser;
use platz1de\EasyEdit\utils\ExtendedBinaryStream;

class PatternArgumentData
{
	private bool $xAxis = false;
	private bool $yAxis = false;
	private bool $zAxis = false;
	private StaticBlock $block;
	private int $realBlock;
	private int $weight = 100;
	/**
	 * @var int[]
	 */
	private array $intData = [];
	/**
	 * @var float[]
	 */
	private array $floatData = [];

	/**
	 * @return bool
	 */
	public function checkXAxis(): bool
	{
		return $this->xAxis;
	}

	/**
	 * @return $this
	 */
	public function useXAxis(): PatternArgumentData
	{
		$this->xAxis = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function checkYAxis(): bool
	{
		return $this->yAxis;
	}

	/**
	 * @return $this
	 */
	public function useYAxis(): PatternArgumentData
	{
		$this->yAxis = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function checkZAxis(): bool
	{
		return $this->zAxis;
	}

	/**
	 * @return $this
	 */
	public function useZAxis(): PatternArgumentData
	{
		$this->zAxis = true;
		return $this;
	}

	/**
	 * @return StaticBlock
	 */
	public function getBlock(): StaticBlock
	{
		return $this->block;
	}

	/**
	 * @return $this
	 */
	public function setBlock(StaticBlock $block): PatternArgumentData
	{
		$this->block = $block;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getRealBlock(): int
	{
		return $this->realBlock;
	}

	/**
	 * @return $this
	 */
	public function setRealBlock(int $block): PatternArgumentData
	{
		$this->realBlock = $block;
		return $this;
	}

	/**
	 * @param string $name
	 * @return int
	 */
	public function getInt(string $name): int
	{
		return $this->intData[$name] ?? -1;
	}

	/**
	 * @param string $name
	 * @param int    $int
	 * @return $this
	 */
	public function setInt(string $name, int $int): PatternArgumentData
	{
		$this->intData[$name] = $int;
		return $this;
	}

	/**
	 * @param string $name
	 * @return float
	 */
	public function getFloat(string $name): float
	{
		return $this->floatData[$name] ?? -1.0;
	}

	/**
	 * @param string $name
	 * @param float  $float
	 * @return $this
	 */
	public function setFloat(string $name, float $float): PatternArgumentData
	{
		$this->floatData[$name] = $float;
		return $this;
	}

	/**
	 * @param array<int, mixed> $args
	 * @return PatternArgumentData
	 */
	public function parseAxes(array &$args): PatternArgumentData
	{
		$result = new self;
		$result->xAxis = in_array("x", $args, true);
		$result->yAxis = in_array("y", $args, true);
		$result->zAxis = in_array("z", $args, true);

		$args = array_diff($args, ["x", "y", "z"]);

		return $result;
	}

	/**
	 * @return PatternArgumentData
	 */
	public static function create(): PatternArgumentData
	{
		return new self;
	}

	/**
	 * @param string $block
	 * @return PatternArgumentData
	 */
	public static function fromBlockType(string $block): PatternArgumentData
	{
		if ($block === "") {
			return new self;
		}
		return self::create()->setBlock(PatternParser::getBlockType($block));
	}

	/**
	 * @return int
	 */
	public function getWeight(): int
	{
		return $this->weight;
	}

	/**
	 * @param int $weight
	 */
	public function setWeight(int $weight): void
	{
		$this->weight = $weight;
	}

	/**
	 * @return string
	 */
	public function fastSerialize(): string
	{
		$stream = new ExtendedBinaryStream();

		$stream->putBool($this->xAxis);
		$stream->putBool($this->yAxis);
		$stream->putBool($this->zAxis);

		if (isset($this->block)) {
			$stream->putBool(true);
			$stream->putString($this->block->fastSerialize());
		} else {
			$stream->putBool(false);
		}

		if (isset($this->realBlock)) {
			$stream->putBool(true);
			$stream->putInt($this->realBlock);
		} else {
			$stream->putBool(false);
		}

		$stream->putInt($this->weight);

		$stream->putInt(count($this->intData));
		foreach ($this->intData as $name => $int) {
			$stream->putString($name);
			$stream->putInt($int);
		}

		$stream->putInt(count($this->floatData));
		foreach ($this->floatData as $name => $float) {
			$stream->putString($name);
			$stream->putFloat($float);
		}

		return $stream->getBuffer();
	}

	/**
	 * @param string $data
	 * @return PatternArgumentData
	 */
	public static function fastDeserialize(string $data): PatternArgumentData
	{
		$stream = new ExtendedBinaryStream($data);
		$result = new self;

		$result->xAxis = $stream->getBool();
		$result->yAxis = $stream->getBool();
		$result->zAxis = $stream->getBool();

		if ($stream->getBool()) {
			//TODO: Add a separate Block-only parser
			/**
			 * @phpstan-ignore-next-line
			 * @noinspection PhpFieldAssignmentTypeMismatchInspection
			 */
			$result->block = Pattern::fastDeserialize($stream->getString());
		}

		if ($stream->getBool()) {
			$result->realBlock = $stream->getInt();
		}

		$result->weight = $stream->getInt();

		for ($i = $stream->getInt(); $i > 0; $i--) {
			$result->intData[$stream->getString()] = $stream->getInt();
		}

		for ($i = $stream->getInt(); $i > 0; $i--) {
			$result->floatData[$stream->getString()] = $stream->getFloat();
		}

		return $result;
	}
}