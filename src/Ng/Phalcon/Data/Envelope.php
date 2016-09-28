<?php
/**
 * Envelope Data
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


use Ng\Phalcon\Models\Abstracts\NgModel;
use Phalcon\Mvc\Model\MetaData as ModelMetaData;

/**
 * Envelope Data
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class Envelope
{
    public function envelope(NgModel $model)
    {
        /** @type ModelMetaData $modelsMetadata */
        $modelsMetadata = $model->getModelsMetaData();

        $data       = array();
        $data['id'] = $model->getId();
        $publics    = $model::getPublicFields();
        $fields     = $modelsMetadata->getDataTypes($model);

        foreach ($publics as $field) {

            if (!isset($fields[$field])) {
                continue;
            }

            $func   = sprintf("get%s", ucfirst($field));
            switch ($fields[$field]) {
                case 0:
                    $data[$field] = (int) $model->$func();
                    if (is_null($model->$func())) {
                        $data[$field]   = null;
                    }
                    break;
                default:
                    $data[$field] = $model->$func();
            }
        }

        return $data;
    }
}
