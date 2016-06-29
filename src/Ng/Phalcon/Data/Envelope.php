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


use Ng\Phalcon\Models\NgModelInterface;

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
    public function envelope(NgModelInterface $model)
    {
        $modelsMetadata = $model->getModelsMetaData();

        $data       = array();
        $data['id'] = (int) $model->getId();
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
                    break;
                default:
                    $data[$field] = $model->$func();
            }
        }

        return $data;
    }
}
