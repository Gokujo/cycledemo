<?php

use Cycle\Annotated;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Database\Config;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\ORM;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

/**
 * Класс для работы с базой данных.
 *
 * @since 171.3.0
 */
abstract class DbHandler {
	/**
	 * Подключение к базе данных
	 *
	 * @var DatabaseManager|null
	 */
	private static ?DatabaseManager $connection = null;
	/**
	 * Объект подключения и управления баз данных.
	 * Необходимо само подключение к базе данных, чтобы получить ORM
	 *
	 * @var ORM\ORM|null
	 */
	private static ?ORM\ORM $manager = null;

	/**
	 * @param    bool    $user_db
	 *
	 * @return DatabaseInterface
	 */
	public static function getDb(bool $user_db = false) : DatabaseInterface {
		self::init();
		$db = $user_db ? 'user' : 'default';
		return self::$connection->database($db);
	}

	/**
	 * Инициирует подключение к базе данных
	 *
	 * @since 171.3.0
	 * @return void
	 */
	private static function init() : void {

		if (is_null(self::$connection)) {

			$db_name_port = explode(':', DBHOST);
			$port         = count($db_name_port) > 1 ? (int) $db_name_port[1] : 3306;
			$host         = $db_name_port[0];

			$dbConfig = new Config\DatabaseConfig([
				'databases'   => [
					'default' => [
						'driver' => 'mysql',
						'prefix' => PREFIX . '_'
					],
					'user'    => [
						'driver' => 'mysql',
						'prefix' => USERPREFIX . '_'
					]
				],
				'connections' => [
					'mysql' => new Config\MySQLDriverConfig(
						connection : new Config\MySQL\TcpConnectionConfig(
							database : DBNAME,
							host : $host,
							port : $port,
							user : DBUSER,
							password : DBPASS,
						),
						reconnect : true,
						timezone : 'Europe/Berlin',
						queryCache : true
					),
				],
			]);

			self::setConnection($dbConfig);
		}

	}

	public static function getManager() : ORM\ORM {
		if (is_null(self::$manager)) self::setManager();
		return self::$manager;
	}

	public static function setManager(?ORM\ORM $manager = null, ?array $model_paths = []) : void {
		if (!is_null($manager)) self::$manager = $manager;
		else {
			if (is_null(self::$manager)) {

				$migrator         = new Migrator($model_paths);
				$registry         = new Registry(self::getConnection());
				$finder           = new Finder();
				$files            = $finder->files()->in($migrator->getModelPaths());
				$classLocator     = new ClassLocator($files);
				$embeddingLocator = new TokenizerEmbeddingLocator($classLocator);
				$entityLocator    = new TokenizerEntityLocator($classLocator);
				$compiler         = new Compiler();

				$schemaArray = $compiler->compile($registry, [
					new Schema\Generator\ResetTables(),
					new Annotated\Embeddings($embeddingLocator),
					new Annotated\Entities($entityLocator),
					new Annotated\TableInheritance(),
					new Annotated\MergeColumns(),
					new Schema\Generator\GenerateRelations(),
					new Schema\Generator\GenerateModifiers(),
					new Schema\Generator\ValidateEntities(),
					new Schema\Generator\RenderTables(),
					new Schema\Generator\RenderRelations(),
					new Schema\Generator\RenderModifiers(),
					new Schema\Generator\ForeignKeys(),
					new Annotated\MergeIndexes(),
					new Schema\Generator\GenerateTypecast(),
				]);

				$schema           = new Cycle\ORM\Schema($schemaArray);
				$factory          = new ORM\Factory(self::getConnection());
				$container        = new Container();
				$commandGenerator = new EventDrivenCommandGenerator($schema, $container);

				$orm = new ORM\ORM(
					factory : $factory,
					schema : $schema,
					commandGenerator : $commandGenerator
				);

				$generator = new Cycle\Schema\Generator\Migrations\GenerateMigrations($migrator->getMigrator()->getRepository(), $migrator->getMigrator()->getConfig());
				$generator->run($registry);
				$migrator->runMigration();
				$migrator->runMigration(true);

				self::$manager = $orm;
			}

		}
	}

	/**
	 * @return DatabaseManager|null
	 */
	public static function getConnection() : ?DatabaseManager {
		self::init();
		return self::$connection;
	}

	/**
	 * @param    Config\DatabaseConfig    $config
	 */
	public static function setConnection(Config\DatabaseConfig $config) : void {
		self::$connection = new DatabaseManager($config);
	}


}