<?php
/**
 * Data Interface
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


use Ng\Phalcon\Data\NgDataInterface;
use Ng\Phalcon\Data\Envelope;
use Ng\Phalcon\Data\Relation;
use Ng\Phalcon\Models\NgModelBase;

use Phalcon\Mvc\Model\Resultset;

/**
 * Data Interface
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class JSON implements NgDataInterface
{
    /** @type Envelope $envelope */
    protected $envelope;

    /** @type Relation $relation */
    protected $relation;

    protected $data     = array();
    protected $linked   = array();

    /** @type Resultset|NgModelBase $src */
    protected $src;

    public function __construct()
    {
        $this->envelope = new Envelope();
        $this->relation = new Relation();
    }

    protected function iterateSrc()
    {
        foreach ($this->src as $src) {
            /** @type NgModelBase $src */
            $this->buildSrc($src);
        }
    }

    protected function buildSrc(NgModelBase $src, $multiple=true)
    {
        $data = $this->envelope->envelope($src);
        $this->relation->getRelations(
            $data, $src, $this->envelope, $this->linked
        );

        if ($multiple == true) {
            $this->data[]   = $data;
        } else {
            $this->data     = $data;
        }

        unset($data);
    }

    public function populate()
    {
        if (is_null($this->src) OR empty($this->src)) {
            return;
        }

        if ($this->src instanceof Resultset) {
            $this->iterateSrc();
            return;
        }

        if ($this->src instanceof NgModelBase) {
            $this->buildSrc($this->src, false);
            return;
        }
    }

    public function getPopulated()
    {
        return array(
            "linked"    => $this->linked,
            "data"      => $this->data,
        );
    }

    public function setSource($src)
    {
        $this->src = $src;
    }
}