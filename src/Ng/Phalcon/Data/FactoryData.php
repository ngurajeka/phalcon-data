<?php
/**
 * Factory Data
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


/**
 * Factory Data
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class FactoryData
{
    const JSON          = "json";
    const UNKNOWN_TYPE  = "Unknown Type";

    protected $type;

    protected $throwException = false;

    public function __construct($type=self::JSON, $throwException=false)
    {
        $this->type             = $type;
        $this->throwException   = $throwException;
    }

    protected function act($msg)
    {
        if ($this->throwException === true) {
            throw new Exception($msg);
        }
    }

    public function populate($src)
    {
        $populated = null;

        try {
            switch ($this->type) {

                case self::JSON:
                    $data       = new JSON\JSON(new NgData($src));
                    $data->buildSource(true);
                    $populated  = $data->getResult();
                    break;

                default:
                    $this->act(self::UNKNOWN_TYPE);
                    break;
            }
        } catch (Exception $e) {
            $this->act($e->getMessage());
        }

        return $populated;
    }

}
