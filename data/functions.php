<?php

@ini_set('pcre.recursion_limit', 10000000);
@ini_set('pcre.backtrack_limit', 10000000);
@ini_set('pcre.jit', false);

if (!function_exists('dirToArray')) {
	/**
	 * Преобразует путь в массив с папками и файлами
	 *
	 * @param    string    $dir
	 * @param    array     $_ext
	 *
	 * @return array
	 */
	function dirToArray(string $dir, array $_ext = []) : array {
		$ext = [
			'.',
			'..',
			'.htaccess',
		];
		foreach ($_ext as $e) {
			if (!in_array($e, $ext)) {
				$ext[] = $e;
			}
		}

		$result = [];

		if (is_dir($dir)) {
			foreach (scandir($dir, SCANDIR_SORT_NONE) as $key => $value) {
				if (!in_array($value, $ext, true)) {
					if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
						$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
					} else {
						$result[] = $value;
					}
				}
			}
		}

		return $result;
	}
}

