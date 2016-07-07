<?php
/**
 * NgDataInterface
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


use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\ModelInterface;

/**
 * NgDataInterface
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
interface NgDataInterface
{
    public function buildSource($fetchRelation=true);

    public function getResult();
}
