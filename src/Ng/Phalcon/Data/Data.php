<?php
/**
 * Data Container
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
 * Data Container
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class Data
{
    const JSON      = "json";
    const UNKNOWN   = "Unknown Type";

    protected $type;
    protected $populated;

    public function __construct($type=self::JSON)
    {
        $this->type = $type;
    }

    public function populate($src)
    {
        try {
            switch ($this->type) {

                case self::JSON:

                    $mod = new JSON\JSON();
                    $mod->setSource($src);
                    $mod->populate();
                    $this->populated = $mod->getPopulated();
                    break;

                default:
                    throw new Exception(self::UNKNOWN);
                    break;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getPopulated()
    {
        return $this->populated;
    }
}
