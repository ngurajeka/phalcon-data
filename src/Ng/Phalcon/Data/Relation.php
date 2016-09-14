<?php
/**
 * Relation
 *
 * PHP Version 5.4.x
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
namespace Ng\Phalcon\Data;


use Ng\Phalcon\Crud\Adapters\SQL\Crud;
use Ng\Phalcon\Crud\Exceptions\Exception as CrudException;
use Ng\Phalcon\Models\Abstracts\NgModel;
use Ng\Query\Adapters\SQL\Conditions\SimpleCondition;
use Ng\Query\Adapters\SQL\Orders\SimpleOrder;
use Ng\Query\Adapters\SQL\Query;
use Ng\Query\Helpers\Operator;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\Relation as ModelRelation;
use Phalcon\Mvc\Model\Resultset;

/**
 * Relation
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class Relation
{
	protected $data       = array();
	protected $linked     = array();
	protected $relations  = array();

	/** @type Envelope $envelope */
	protected $envelope;

	protected $belongsToIds = array();
	protected $hasManyIds   = array();
	protected $hasOneIds    = array();

	final protected function belongsTo(ModelRelation $relation)
	{
		// checking options from relations
		$opts       = $relation->getOptions();
		if (!isset($opts["alias"])) {
			return;
		}

		$fetch		= true;
		if (array_key_exists("fetch", $opts) && is_bool($opts["fetch"])) {
			$fetch	= $opts["fetch"];
		}

		if (!$fetch) {
			return;
		}

		$autoLimit      = true;
		if (array_key_exists("limit", $opts) && is_bool($opts["limit"])) {
			$autoLimit  = $opts["limit"];
		}

		// build local needed variable
		$field  = $relation->getFields();

		$reference      = $relation->getReferencedFields();
		/** @var NgModel $modelRelation */
		$modelRelation  = $relation->getReferencedModel();

		// check if related field exist or not
		if (!isset($this->data[$field])) {
			return;
		}

		if (!$this->data[$field]) {
			return;
		}

		// build data.links
		$this->data["links"][$reference] = (int) $this->data[$field];

		if (!isset($this->belongsToIds[$field])) {
			$this->belongsToIds[$field]	= array();
		}

		// check if data[related] already populated
		if (in_array($this->data[$field], $this->belongsToIds[$field])) {
			return;
		}

		// store to haystack
		$this->belongsToIds[$field][] = $this->data[$field];

		$query = new Query($autoLimit);
		$query->addCondition(
			new SimpleCondition($reference, Operator::OP_EQUALS, $this->data[$field])
		);

		$sort      = SimpleOrder::ORDER_ASC;
		if (array_key_exists("sort", $opts) && is_string($opts["sort"])) {
			$sort  = $opts["sort"];
		}

		$query->addOrder(
			new SimpleOrder($modelRelation::getPrimaryKey(), $sort)
		);

		// fetch model data, otherwise throw an exception
		try {
			$handler        = new Crud();
			/** @var $relationModel NgModel */
			$relationModel  = $handler->read(new $modelRelation, $query, true);
			unset($handler);
		} catch (CrudException $e) {
			throw new Exception($e->getMessage());
		}

		// check if the model was an instance of NgModel
		if (!$relationModel instanceof NgModel) {
			return;
		}

		// envelope relationModel to get relation data
		$relationData = $this->envelope->envelope($relationModel);

		// check if linked[reference] already populated
		if (!isset($this->linked[$reference])) {
			$this->linked[$reference]       = array();
		}

		// put relation data on linked
		$this->linked[$reference][]         = $relationData;

		// remove data[field]
		unset($this->data[$field]);
	}

	final protected function hasOne(
		NgModel $model, ModelRelation $relation
	) {

		// check options for alias
		$opts       = $relation->getOptions();
		if (!isset($opts["alias"])) {
			return;
		}

		$fetch		= true;
		if (array_key_exists("fetch", $opts) && is_bool($opts["fetch"])) {
			$fetch	= $opts["fetch"];
		}

		if (!$fetch) {
			return;
		}

		$autoLimit      = true;
		if (array_key_exists("limit", $opts) && is_bool($opts["limit"])) {
			$autoLimit  = $opts["limit"];
		}

		// build needed variable(s)
		$references     = $relation->getReferencedFields();
		/** @var NgModel $modelRelation */
		$modelRelation  = $relation->getReferencedModel();

		$query = new Query($autoLimit);
		$query->addCondition(
			new SimpleCondition($references, Operator::OP_EQUALS, $model->getId())
		);
		$sort      = SimpleOrder::ORDER_ASC;
		if (array_key_exists("sort", $opts) && is_string($opts["sort"])) {
			$sort  = $opts["sort"];
		}

		$query->addOrder(
			new SimpleOrder($modelRelation::getPrimaryKey(), $sort)
		);

		// fetch resultset
		try {
			$handler    = new Crud();
			/** @type NgModel $ngModel */
			$ngModel    = $handler->read(new $modelRelation, $query, true);
			unset($handler);
		} catch (CrudException $e) {
			throw new Exception($e->getMessage());
		}

		if (!$ngModel instanceof NgModel) {
			return;
		}

		// check and prepare data.links
		if (!isset($this->data["links"][$references])) {
			$this->data["links"][$references] = array();
		}

		// check and prepare linked
		if (!isset($this->linked[$references])) {
			$this->linked[$references] = array();
		}

		/** @type NgModel $ngModel */
		// check if this model already populated
		if (!isset($this->hasOneIds[$references])) {
			$this->hasOneIds[$references] = array();
		}

		if (in_array($ngModel->getId(), $this->hasOneIds)) {
			return;
		}

		$this->hasOneIds[$references][]	= $ngModel->getId();

		// check if this model already in our data.links
		if (in_array($ngModel->getId(), $this->data["links"][$references])) {
			return;
		}

		// put relation id on data.links
		$this->data["links"][$references][] = (int) $ngModel->getId();

		// envelope model into relation data
		$relationData   = $this->envelope->envelope($ngModel);

		// check if relationData already in our linked
		if (in_array($relationData, $this->linked[$references])) {
			return;
		}

		// put relation data on our linked
		$this->linked[$references][] = $relationData;
	}

	final protected function hasMany(
		NgModel $model, ModelRelation $relation
	) {

		// check options for alias
		$opts       = $relation->getOptions();
		if (!isset($opts["alias"])) {
			return;
		}

		$fetch		= true;
		if (array_key_exists("fetch", $opts) && is_bool($opts["fetch"])) {
			$fetch	= $opts["fetch"];
		}

		if (!$fetch) {
			return;
		}

		$autoLimit      = true;
		if (array_key_exists("limit", $opts) && is_bool($opts["limit"])) {
			$autoLimit  = $opts["limit"];
		}

		// build needed variable(s)
		$references     = $relation->getReferencedFields();
		/** @var NgModel $modelRelation */
		$modelRelation  = $relation->getReferencedModel();

		$query = new Query($autoLimit);
		$query->addCondition(
			new SimpleCondition($references, Operator::OP_EQUALS, $model->getId())
		);

		$sort      = SimpleOrder::ORDER_ASC;
		if (array_key_exists("sort", $opts) && is_string($opts["sort"])) {
			$sort  = $opts["sort"];
		}

		$query->addOrder(
			new SimpleOrder($modelRelation::getPrimaryKey(), $sort)
		);

		// fetch resultset
		try {
			$handler    = new Crud();
			/** @type Resultset $resultSet */
			$resultSet  = $handler->read(new $modelRelation, $query, false);
			unset($handler);
		} catch (CrudException $e) {
			throw new Exception($e->getMessage());
		}

		// check and prepare data.links
		if (!isset($this->data["links"][$references])) {
			$this->data["links"][$references] = array();
		}

		// check and prepare linked
		if (!isset($this->linked[$references])) {
			$this->linked[$references] = array();
		}

		foreach ($resultSet as $ngModel) {
			/** @type NgModel $ngModel */
			// check if this model already populated
			if (!isset($this->hasManyIds[$references])) {
				$this->hasManyIds[$references] = array();
			}

			if (in_array($ngModel->getId(), $this->hasManyIds)) {
				continue;
			}

			$this->hasManyIds[$references][] = $ngModel->getId();

			// check if this model already in our data.links
			if (in_array($ngModel->getId(), $this->data["links"][$references])) {
				continue;
			}

			// put relation id on data.links
			$this->data["links"][$references][] = (int) $ngModel->getId();

			// envelope model into relation data
			$relationData   = $this->envelope->envelope($ngModel);

			// check if relationData already in our linked
			if (in_array($relationData, $this->linked[$references])) {
				continue;
			}

			// put relation data on our linked
			$this->linked[$references][] = $relationData;
		}
	}

	public function getRelations(
		array &$data, NgModel $model, Envelope $envelope, array &$linked
	) {

		if (!isset($data["links"])) {
			$data["links"]  = array();
		}

		$this->data         = $data;
		$this->envelope     = $envelope;
		$this->linked       = $linked;
		$this->fetchRelationUsingModelsManager($model);

		$data   = $this->data;
		$linked = $this->linked;
	}

	private function fetchRelationUsingModelsManager(NgModel $model)
	{
		/** @var ModelManager $modelsManager */
		$modelsManager = $model->getModelsManager();

		foreach ($modelsManager->getBelongsTo($model) as $relation) {
			$this->belongsTo($relation);
		}

		foreach ($modelsManager->getHasMany($model) as $relation) {
			$this->hasMany($model, $relation);
		}

		foreach ($modelsManager->getHasOne($model) as $relation) {
			$this->hasOne($model, $relation);
		}
	}
}
