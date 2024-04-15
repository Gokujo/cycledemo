<?php

use Cycle\Database\StatementInterface;
use Cycle\ORM;
use Spiral\Pagination\Paginator;

/**
 * Класс для работы с базой данных
 *
 * @since 171.3.0
 */
class MhDB {
	private ?ORM\ORM           $orm    = null;
	private ?ORM\EntityManager $em     = null;
	private object|string|null $entity = null;

	public function __construct(object|string $schema, string|array|null $model_paths = []) {
		$this->setOrm($schema, $model_paths);
		$this->setManager();
	}

	private function setOrm(object|string $schema, string|array|null $model_paths = []) : void {
		DbHandler::setManager(model_paths : $model_paths);
		$this->orm = DbHandler::getManager();
		$this->setEntity($schema);

	}

	public function setManager() : void {
		$this->em = new ORM\EntityManager($this->orm);
	}

	public function getManager() : ORM\EntityManager {
		return $this->em;
	}

	/**
	 * Удаляет объект по его первичному ключу.
	 *
	 * @throws Throwable
	 */
	public function delete(int $pk) : ORM\Transaction\StateInterface {
		$obj = $this->get($pk);
		return $this->getManager()->delete($obj)->run();
	}

	/**
	 * Возвращает объект по его первичному ключу
	 *
	 * @param    int    $pk
	 *
	 * @return object|null
	 */
	public function get(int $pk) : ?object {
		return $this->repository()->findByPK($pk);
	}

	/**
	 * Возвращает репозиторий сущности
	 *
	 * @return ORM\RepositoryInterface
	 */
	public function repository() : ORM\RepositoryInterface {
		if (gettype($this->entity) === 'string') return $this->getOrm()->getRepository($this->entity);
		else return $this->getOrm()->getRepository($this->getEntity()::class);
	}

	public function getOrm() : ?ORM\ORM {
		return $this->orm;
	}

	public function getEntity() : object {
		return $this->entity;
	}

	/**
	 * Устанавливает тип сущности для дальнейшей работы с ней
	 *
	 * @param    object|string|null    $entity    Класс сущности / объекта
	 *
	 * @return void
	 */
	public function setEntity(object|string|null $entity) : void {
		if ($entity === null) return;

		if (gettype($entity) === 'string') {
			$entity = new $entity();
		}
		$this->entity = $entity;
	}

	/**
	 * Выполняет запросы создания и изменения модели.
	 * Передаётся объект сущности.
	 * Используется для создания и изменения объекта.
	 *
	 * @throws Throwable
	 */
	public function run(object $entity) : ORM\Transaction\StateInterface {

		return $this->getManager()->persist($entity)->run();
	}

	/**
	 * Создаёт объект
	 *
	 * @throws Throwable
	 */
	public function create(object $entity) : ORM\Transaction\StateInterface {
		return $this->run($entity);
	}

	/**
	 * Изменяет объект
	 *
	 * @throws Throwable
	 */
	public function update(object $entity) : ORM\Transaction\StateInterface {
		return $this->run($entity);
	}

	/**
	 * Выполняет прямой запрос SQL к базе данных
	 *
	 * @param    string    $sql       Указывается SQL запрос с параметрами либо без
	 * @param    array     $params    Параметры в виде массива: каждый элемент массива - это параметр и его значение,
	 *                                что заменяется в запросе
	 *
	 * @return StatementInterface
	 */
	public function query(string $sql, array $params = []) : StatementInterface {
		$dbal = DbHandler::getDb();
		return $dbal->query($sql, $params);
	}

	/**
	 * Возвращает класс пагинации для удобной навигации
	 *
	 * @param    string    $orderby    Параметр по которому будет идти сортировка данных
	 * @param    string    $sortby     Параметр указывающий порядок сортировки
	 * @param    int       $limit      Параметр указывающий сколько записей показывать на страницу
	 * @param    int       $page       Параметр указывающий на страницу с которой будет выводить данные
	 *
	 * @return ORM\Select
	 */
	public function paginate(string $orderby, string $sortby = 'DESC', int $limit = 10, int $page = 1) : ORM\Select {
		$select    = $this->repository()->select()->orderBy($orderby, $sortby);
		$paginator = new Paginator($limit);
		$paginator->withPage($page)->paginate($select);

		return $select;
	}

	/**
	 * Возвращает количество записей в базе данных
	 *
	 * @return int
	 */
	public function count() : int {
		return $this->repository()->select()->count();
	}
}