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
    protected $throwException = false;
    protected $populated;

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
        try {
            switch ($this->type) {

                case self::JSON:

                    $mod = new JSON\JSON();
                    $mod->setSource($src);
                    $mod->populate();
                    $this->populated = $mod->getPopulated();
                    break;

                default:
                    $this->act(self::UNKNOWN);
                    break;
            }
        } catch (Exception $e) {
            $this->act($e->getMessage());
        }
    }

    public function getPopulated()
    {
        return $this->populated;
    }
}
