<?php
//===============================================================
// Файл: DataManager.php                                        =
// Путь: engine/inc/maharder/_includes/classes/DataManager.php  =
// Дата создания: 2024-03-22 06:41:38                           =
// Последнее изменение: 2024-03-22 06:41:37                     =
// ==============================================================
// Автор: Maxim Harder <dev@devcraft.club> © 2024               =
// Сайт: https://devcraft.club                                  =
// Телеграм: http://t.me/MaHarder                               =
// ==============================================================
// Менять на свой страх и риск!                                 =
// Код распространяется по лицензии MIT                         =
//===============================================================
abstract class DataManager {

	/**
	 * Возвращает указанный путь в виде массива со всеми папками и файлами в нём
	 *
	 * @param             $dir    //  Путь, который нужно просканировать
	 * @param    mixed    ...$_ext
	 *
	 * @return array
	 * @version 2.0.9
	 */
	public static function dirToArray(string $dir, ...$_ext) : array {
		return dirToArray($dir, $_ext);
	}

	/**
	 * Создаёт папку по указанному пути
	 *
	 * @param    string    $service
	 * @param    string    $module
	 * @param    int       $permission
	 * @param    string    ...$_path    Может содержать несколько путей, которые будут объедены в один
	 *
	 * @throws JsonException
	 * @return bool
	 */
	public static function createDir(string $service = 'DataManager', string $module = 'mhadmin', int $permission = 0755, ...$_path) : bool {
		foreach ($_path as $path) {
			try {
				if (!mkdir($path, $permission, true) && !is_dir($path)) {
					throw new Exception($module, "Путь \"{$path}\" не был создан");
				}
			} catch (Exception $e) {
				var_dump($e);
			}
		}

		return true;
	}

	/**
	 * Объединяет несколько путей в один
	 * Объединение происходит слева направо
	 *
	 * @version 2.0.9
	 * @since   2.0.9
	 *
	 * @param ...$_path
	 *
	 * @return string
	 */
	public static function joinPaths(...$_path) : string {
		return self::normalize_path(implode(DIRECTORY_SEPARATOR, $_path));
	}

	/**
	 * Удаляет полностью путь и файлы
	 *
	 * @version 2.0.9
	 * @since   2.0.9
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public static function deleteDir($path) : void {
		foreach (self::dirToArray($path) as $f) {
			$new_path = self::joinPaths($path, $f);
			if (is_file($new_path)) @unlink($new_path);
			else {
				if (is_array($f)) {
					foreach ($f as $f2) {
						self::deleteDir(self::joinPaths($path, $f2));
					}
				} else {
					self::deleteDir($new_path);
					@rmdir($new_path);
				}
			}

		}
		@rmdir($path);
	}

	/**
	 * Преобразует ... аргументы в понятный массив
	 *
	 * @param    array|null    $args
	 *
	 * @return array
	 */
	public static function nameArgs(?array $args) : array {
		$returnArr = [];

		array_walk_recursive($args, function ($arg, $id) use (&$returnArr) {
			if (is_numeric($id) && is_array($arg)) {
				$returnArr = array_merge($returnArr, self::nameArgs($arg));
			} elseif (is_numeric($id) && !is_array($arg)) {
				$returnArr[$arg] = $arg;
			} elseif (is_array($id)) {
				$returnArr = array_merge($returnArr, self::nameArgs($id));
			} else {
				$returnArr[$id] = $arg;
			}
		});

		return array_filter($returnArr, static function ($value) { return !is_null($value) && $value !== ''; });
	}

	/**
	 * Проверяет файл на верный тип и конвертирует его
	 *
	 * @param $value    //  Значение
	 * @param $type     //  Проверяемый тип файла
	 *
	 * @return bool|float|int|string
	 */
	public static function defType($value, $type) : float|bool|int|string {
		if (in_array($type, [
			'double',
			'float',
		])) {
			$output = (float) $value;
		} elseif (in_array($type, [
			'boolean',
			'bool',
		])) {
			$output = (bool) $value;
		} elseif (in_array($type, [
			'integer',
			'int',
			'tinyint',
		])) {
			$output = (int) $value;
		} else {
			$output = "'{$value}'";
		}

		return $output;
	}

	/**
	 * Обрабатывает значение на сверяющие знаки и возвращает в нужном параметре обратно
	 *
	 * @param $value    //  Значение со знаками сравнения в начале
	 *
	 * @return string
	 */
	public static function getComparer($value) : string {
		$firstSign  = [
			'!',
			'<',
			'>',
			'%',
		];
		$secondSign = ['='];
		$type       = gettype($value);
		$outSign    = '=';
		$checkSign  = null;

		if (!in_array($type, [
				'integer',
				'double',
				'boolean',
			]) && in_array($value[0], $firstSign, true)) {
			$checkSign = $value[0];
			if ($value[1] === $secondSign) {
				$checkSign .= $value[1];
				$value     = substr($value, 2);
			} else {
				$value = substr($value, 1);
			}
		}

		if ($checkSign === '!') {
			$outSign = '<>';
		} elseif (in_array($checkSign, [
			'<',
			'>',
			'<=',
			'>=',
		])) {
			$outSign = $checkSign;
		} elseif ($checkSign === '%') {
			$outSign = 'LIKE';
			$value   = '%' . $value . '%';
		}

		$value = self::defType($value, $type);

		return " {$outSign} {$value}";
	}


	/**
	 * Позаимствованная функция DLE
	 * Добавлена проверка пути
	 *
	 * @param $path
	 *
	 * @return array|string|string[]
	 */
	public static function normalize_path($path) : array|string {

		$path = trim(str_replace(chr(0), '', (string) $path));
		$path = str_replace(array('/', '\\'), '/', $path);

		if (!$path) return '';

		if (preg_match('#\p{C}+#u', $path)) {
			return '';
		}

		$path_parts = pathinfo($path);

		$filename = $path_parts['basename'];

		$parts = array_filter(explode('/', $path_parts['dirname']), 'strlen');

		$absolutes = array();

		foreach ($parts as $part) {
			$part = trim($part);

			if ('.' === $part || '..' === $part || !$part) {
				continue;
			}

			$absolutes[] = $part;
		}

		$path = implode('/', $absolutes);

		if ($path) {
			$path .= '/';
		}

		if ($filename) {
			$path .= $filename;
		}

		$root = ROOT_DIR . '/';

		if (stripos($path, $root) === 0) {
			$path = str_ireplace($root, '', $path);
		}

		if (function_exists('mb_substr')) {
			$first_sign = mb_substr($path, 0, 1);
		} else {
			$first_sign = $path[0];
		}
		if ('/' !== $first_sign && stripos(PHP_OS_FAMILY, 'LIN') === 0) {
			$path = '/' . $path;
		}

		return $path;

	}
}