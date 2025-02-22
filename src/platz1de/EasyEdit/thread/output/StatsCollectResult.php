<?php

namespace platz1de\EasyEdit\thread\output;

use platz1de\EasyEdit\cache\TaskCache;
use platz1de\EasyEdit\utils\ExtendedBinaryStream;
use platz1de\EasyEdit\utils\ReferencedWorldHolder;

class StatsCollectResult extends OutputData
{
	use ReferencedWorldHolder;

	private int $cacheId;
	private string $taskName;
	private int $taskId;
	private string $responsiblePlayer;
	private int $totalPieces;
	private int $piecesLeft;
	private int $queueLength;
	private int $storageSize;
	private int $currentMemory;
	private int $realMemory;

	/**
	 * @param int    $cacheId
	 * @param string $taskName
	 * @param int    $taskId
	 * @param string $responsiblePlayer
	 * @param int    $totalPieces
	 * @param int    $piecesLeft
	 * @param int    $queueLength
	 * @param int    $storageSize
	 * @param int    $currentMemory
	 * @param int    $realMemory
	 */
	public static function from(int $cacheId, string $taskName, int $taskId, string $responsiblePlayer, int $totalPieces, int $piecesLeft, int $queueLength, int $storageSize, int $currentMemory, int $realMemory): void
	{
		$data = new self();
		$data->cacheId = $cacheId;
		$data->taskName = $taskName;
		$data->taskId = $taskId;
		$data->responsiblePlayer = $responsiblePlayer;
		$data->totalPieces = $totalPieces;
		$data->piecesLeft = $piecesLeft;
		$data->queueLength = $queueLength;
		$data->storageSize = $storageSize;
		$data->currentMemory = $currentMemory;
		$data->realMemory = $realMemory;
		$data->send();
	}

	public function handle(): void
	{
		TaskCache::get($this->cacheId)($this->taskName, $this->taskId, $this->responsiblePlayer, $this->totalPieces, $this->piecesLeft, $this->queueLength, $this->storageSize, $this->currentMemory, $this->realMemory);
	}

	public function putData(ExtendedBinaryStream $stream): void
	{
		$stream->putInt($this->cacheId);
		$stream->putString($this->taskName);
		$stream->putInt($this->taskId);
		$stream->putString($this->responsiblePlayer);
		$stream->putInt($this->totalPieces);
		$stream->putInt($this->piecesLeft);
		$stream->putInt($this->queueLength);
		$stream->putInt($this->storageSize);
		$stream->putInt($this->currentMemory);
		$stream->putInt($this->realMemory);
	}

	public function parseData(ExtendedBinaryStream $stream): void
	{
		$this->cacheId = $stream->getInt();
		$this->taskName = $stream->getString();
		$this->taskId = $stream->getInt();
		$this->responsiblePlayer = $stream->getString();
		$this->totalPieces = $stream->getInt();
		$this->piecesLeft = $stream->getInt();
		$this->queueLength = $stream->getInt();
		$this->storageSize = $stream->getInt();
		$this->currentMemory = $stream->getInt();
		$this->realMemory = $stream->getInt();
	}
}