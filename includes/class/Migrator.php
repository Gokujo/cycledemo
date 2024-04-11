<?php
use Cycle\Migrations;
use Cycle\Migrations\Capsule;
use Cycle\Migrations\Config\MigrationConfig;

/**
 * Класс для работы с миграциями
 * Проверяет есть ли миграции для моделей базы данных
 * Если такие есть, то выполняет миграции
 *
 * Создаёт так-же соединение к базе данных
 *
 * @since 171.3.0
 *
 * */
class Migrator {
	/**
	 * Мигратор, который проверяет наличие новых миграций и выполняет их
	 *
	 * @var Migrations\Migrator|null
	 */
	private ?Migrations\Migrator $migrator = null;
	/**
	 * Массив путей к моделям
	 *
	 * @var array
	 */
	private array $model_paths = array();

	public function __construct(string|array|null $model_paths = []) {
		global $mh_models_paths;
		$this->setModelPaths($mh_models_paths);
		if (!is_null($model_paths)) $this->setModelPaths($model_paths);
	}

	public function getModelPaths() : array {
		return $this->model_paths ?? [];
	}



	/**
	 * Устанавливает пути к моделям
	 *
	 * @param    array|string    $model_paths    Путь к моделям
	 *
	 * @return void
	 */
	public function setModelPaths(array|string $model_paths) : void {
		if (is_array($model_paths)) {
			$this->model_paths = array_merge($model_paths, $this->model_paths);
		} else {
			$this->model_paths[] = $model_paths;
		}

		foreach ($this->model_paths as $id => $model_path) {
			$this->model_paths[$id] = DataManager::normalize_path($model_path);
		}
	}

	public function runMigration(bool $user_db = false) : void {
		$db = $user_db ? 'user' : 'default';
		$this->getMigrator()->run(new Capsule(DbHandler::getConnection()->database($db)));
	}

	/**
	 * Создаёт / проверят наличие новых миграций с базой данных.
	 * Миграции хранятся по установленному пути
	 *
	 * @since 171.3.0
	 * @return Migrations\Migrator
	 */
	public function getMigrator() : Migrations\Migrator {

		if (is_null($this->migrator)) {
			$migratorConfig = new MigrationConfig([
				'directory' => DataManager::normalize_path(INCLUDES_DIR . '/migrations/'),
				'table'     => 'maharder_migrations',
				'safe'      => true
			]);

			$migrator = new Migrations\Migrator($migratorConfig, DbHandler::getConnection(), new Migrations\FileRepository($migratorConfig));

			$migrator->configure();

			$this->migrator = $migrator;
		}

		return $this->migrator;
	}

}