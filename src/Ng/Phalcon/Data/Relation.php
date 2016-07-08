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


use Ng\Query\Query;
use Ng\Query\Condition\ArrayCondition;
use Ng\Query\Condition\SimpleCondition;
use Ng\Query\Operator;
use Ng\Phalcon\Crud\Crud;
use Ng\Phalcon\Crud\Exception as CrudException;
use Ng\Phalcon\Models\NgModelBase;

use Phalcon\Mvc\Model\Exception as ModelException;
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

    final protected function belongsTo(
        NgModelBase $model, ModelRelation $relation
    ) {

        // checking options from relations
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build local needed variable
        $field  = $relation->getFields();

        $reference      = $relation->getReferencedFields();
        $modelRelation  = $relation->getReferencedModel();

        // check if related field exist or not
        if (!isset($this->data[$field])) {
            return;
        }

        // build data.links
        $this->data["links"][$reference] = (int) $this->data[$field];

        // check if data[related] already populated
        if (in_array($this->data[$field], $this->belongsToIds)) {
            return;
        }

        // store to haystack
        $this->belongsToIds[] = $this->data[$field];

        $query = new Query();
        $query->addCondition(
            new SimpleCondition($reference, Operator::OP_EQUALS, $this->data[$field])
        );

        // fetch model data, otherwise throw an exception
        try {
            $handler        = new Crud();
            /** @var $relationModel NgModelBase */
            $relationModel  = $handler->read(new $modelRelation, $query, true);
            unset($handler);
        } catch (CrudException $e) {
            throw new Exception($e->getMessage());
        }

        // check if the model was an instance of NgModel
        if (!$relationModel instanceof NgModelBase) {
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
        NgModelBase $model, ModelRelation $relation
    ) {

        // check options for alias
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build needed variable(s)
        $references     = $relation->getReferencedFields();
        $modelRelation  = $relation->getReferencedModel();

        $query = new Query();
        $query->addCondition(
            new SimpleCondition($reference, Operator::OP_EQUALS, $this->data[$field])
        );

        // fetch resultset
        try {
            $handler    = new Crud();
            /** @type NgModelBase $ngModel */
            $ngModel    = $handler->read(new $modelRelation, $query, true);
            unset($handler);
        } catch (CrudException $e) {
            throw new Exception($e->getMessage());
        }

        if (!$ngModel instanceof NgModelBase) {
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

        /** @type NgModelBase $ngModel */
        // check if this model already populated
        if (in_array($ngModel->getId(), $this->hasOneIds)) {
            return;
        }

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
        NgModelBase $model, ModelRelation $relation
    ) {

        // check options for alias
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build needed variable(s)
        $references     = $relation->getReferencedFields();
        $modelRelation  = $relation->getReferencedModel();

        $query = new Query();
        $query->addCondition(
            new SimpleCondition($references, Operator::OP_EQUALS, $model->getId())
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
            /** @type NgModelBase $ngModel */
            // check if this model already populated
            if (in_array($ngModel->getId(), $this->hasManyIds)) {
                continue;
            }

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
        array &$data, NgModelBase $model, Envelope $envelope, array &$linked
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

    private function fetchRelationUsingModelsManager(NgModelBase $model)
    {
        $modelsManager = $model->getModelsManager();

        foreach ($modelsManager->getBelongsTo($model) as $relation) {
            $this->belongsTo($model, $relation);
        }

        foreach ($modelsManager->getHasMany($model) as $relation) {
            $this->hasMany($model, $relation);
        }

        foreach ($modelsManager->getHasOne($model) as $relation) {
            $this->hasOne($model, $relation);
        }
    }
}
