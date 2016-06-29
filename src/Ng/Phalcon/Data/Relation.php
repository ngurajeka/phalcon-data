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


use Ng\Phalcon\Models\NgModelInterface;

use Phalcon\Mvc\Model\Relation as ModelRelation;

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
        NgModelInterface $model, ModelRelation $relation
    ) {

        // checking options from relations
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build local needed variable
        $alias      = $opts["alias"];
        $field      = $relation->getFields();
        $reference  = $relation->getReferencedFields();

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

        // fetch model data, otherwise throw an exception
        try {
            $relationModel = $model->{$alias};
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        // check if the model was an instance of NgModel
        if (!$relationModel instanceof NgModelInterface) {
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
        NgModelInterface $model, ModelRelation $relation
    ) {

        // check options for alias
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build needed variable(s)
        $alias      = $opts["alias"];
        $references = $relation->getReferencedFields();

        // fetch resultset
        try {
            /** @type NgModelInterface $ngModel */
            $ngModel = $model->{$alias};
        } catch (/Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$ngModel instanceof NgModelInterface) {
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

        /** @type NgModelInterface $ngModel */
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
        NgModelInterface $model, ModelRelation $relation
    ) {

        // check options for alias
        $opts       = $relation->getOptions();
        if (!isset($opts["alias"])) {
            return;
        }

        // build needed variable(s)
        $alias      = $opts["alias"];
        $references = $relation->getReferencedFields();

        // fetch resultset
        try {
            /** @type Resultset $resultSet */
            $resultSet = $model->{$alias};
        } catch (Exception $e) {
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
            /** @type NgModelInterface $ngModel */
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

    private function fetchRelationUsingModelsManager(NgModelInterface $model)
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
