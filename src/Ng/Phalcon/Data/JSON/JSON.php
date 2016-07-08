<?php
/**
 * JSON Builder
 *
 * PHP Version 5.4.x
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
namespace Ng\Phalcon\Data\JSON;


use Ng\Phalcon\Data\AbstractNgData;

/**
 * JSON Builder
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class JSON extends AbstractNgData
{
    protected $data = array();

    /**
     * Stringify Result from data builder
     *
     * @return string
     */
    public function stringifyResult()
    {
        return json_encode($this->getResult());
    }

    /**
     * Decode Result from data builder
     *
     * @param boolean $associative
     *
     * @return \stdClass|array
     */
    public function decodeResult($associative=false)
    {
        return json_decode($this->stringifyResult(), $associative);
    }

    /**
     * Build Source Data using wrapper builder
     *
     * @param boolean $fetchRelation
     *
     * @return void
     */
    public function buildSource($fetchRelation=true)
    {
        $this->wrapper->buildSource($fetchRelation);
        $this->data = $this->wrapper->getResult();
    }

    /**
     * Get the result from data builder
     *
     * @return array
     */
    public function getResult()
    {
        $result = array("data" => array(), "linked" => array());
        if (isset($this->data["data"])) {
            $result["data"]     = $this->data["data"];
        }

        if (isset($this->data["relations"])) {
            $result["linked"]   = $this->data["relations"];
        }

        return $result;
    }
}
