<?php

namespace App\Utils;

use Symfony\Component\Finder\Finder;

class FileReader
{
	public function getLines($filename): array
	{
		$finder = new Finder();
		$finder->files()->in(__DIR__.'/../../')->name($filename);
		if (!$finder->hasResults()) {
			throw new \Exception('File not found');
		}
		foreach ($finder as $file) {
			$lines = explode(PHP_EOL, $file->getContents());
			return $lines;
		}
		return [];
	}
}