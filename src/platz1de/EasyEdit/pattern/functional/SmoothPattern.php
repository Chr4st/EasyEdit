<?php

namespace platz1de\EasyEdit\pattern\functional;

use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\SelectionContext;
use platz1de\EasyEdit\utils\HeightMapCache;
use platz1de\EasyEdit\utils\SafeSubChunkExplorer;

class SmoothPattern extends Pattern
{
	/**
	 * @param int                  $x
	 * @param int                  $y
	 * @param int                  $z
	 * @param SafeSubChunkExplorer $iterator
	 * @param Selection            $current
	 * @param Selection            $total
	 * @return int
	 */
	public function getFor(int $x, int $y, int $z, SafeSubChunkExplorer $iterator, Selection $current, Selection $total): int
	{
		HeightMapCache::load($iterator, $current);

		$max = 0;
		$tMax = 0;
		$min = 0;
		$tMin = 0;
		for ($kernelX = -1; $kernelX <= 1; $kernelX++) {
			for ($kernelZ = -1; $kernelZ <= 1; $kernelZ++) {
				$m = HeightMapCache::getHighest($x + $kernelX, $z + $kernelZ);
				if ($m !== null) {
					$max += $m;
					$tMax++;
				}

				$m = HeightMapCache::getLowest($x + $kernelX, $z + $kernelZ);
				if ($m !== null) {
					$min += $m;
					$tMin++;
				}
			}
		}
		if ($tMax !== 0) {
			$max /= $tMax;
		} elseif ($tMin !== 0) {
			$max = $current->getCubicEnd()->getY();
		}
		if ($tMin !== 0) {
			$min /= $tMin;
		}
		$max = round($max);
		$min = round($min);
		$oMax = HeightMapCache::getHighest($x, $z) ?? (int) $current->getCubicEnd()->getY();
		$oMin = HeightMapCache::getLowest($x, $z) ?? (int) $current->getCubicStart()->getY();
		$oMid = ($oMin + $oMax) / 2;
		$mid = ($min + $max) / 2;

		if ($tMin >= 5 && $min !== $max) {
			if ($y >= $mid && $y <= $max) {
				$k = ($y - $mid) / ($max - $mid);
				$gy = $oMid + round($k * ($oMax - $oMid));
				return $iterator->getBlockAt($x, (int) $gy, $z);
			}

			if ($y <= $mid && $y >= $min) {
				$k = ($y - $mid) / ($min - $mid);
				$gy = $oMid + round($k * ($oMin - $oMid));
				return $iterator->getBlockAt($x, (int) $gy, $z);
			}
		}

		return 0;
	}

	/**
	 * @param SelectionContext $context
	 */
	public function applySelectionContext(SelectionContext $context): void
	{
		$context->includeWalls()->includeVerticals()->includeFilling();
	}
}