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


use Ng\Phalcon\Models\NgModelBase;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\ModelInterface;

/**
 * Data Container
 *
 * @category Library
 * @package  Library
 * @author   Ady Rahmat MA <adyrahmatma@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/ngurajeka/phalcon-data
 */
class NgData implements NgDataInterface
{
    const INVALID_SOURCE = "Invalid Source";

    protected $fetchRelation = true;
    protected $source;

    /** @type Envelope $envelope */
    protected $envelope;
    /** @type Relation $relation */
    protected $relation;

    protected $data         = array();
    protected $relations    = array();

    public function __construct($source)
    {
        if (is_null($source) OR empty($source)) {
            throw new Exception(self::INVALID_SOURCE);
        } else if (!$source instanceOf Resultset
            AND !$source instanceOf ModelInterface) {
            throw new Exception(self::INVALID_SOURCE);
        }

        $this->source   = $source;
        $this->envelope = new Envelope();
        $this->relation = new Relation();
    }

    /**
     * Build Source Data
     *
     * @param bool $fetchRelation
     */
    public function buildSource($fetchRelation=true)
    {
        $this->fetchRelation = $fetchRelation;

        if ($this->source instanceOf Resultset) {
            $this->iterate();
            return;
        }

        if ($this->source instanceOf ModelInterface) {
            $this->build($this->source, false);
            return;
        }
    }

    private function iterate()
    {
        foreach ($this->source as $src) {
            /** @type NgModelBase $src */
            $this->build($src);
        }
    }

    /**
     * Build The source
     *
     * @param NgModelBase $src
     * @param boolean     $multiple
     *
     * @return void
     */
    private function build(NgModelBase $src, $multiple=true)
    {
        $data = $this->envelope->envelope($src);

        if ($this->fetchRelation) {
            $this->relation->getRelations(
                $data, $src, $this->envelope, $this->relations
            );
        }

        if ($multiple == true) {
            $this->data[]   = $data;
        } else {
            $this->data     = $data;
        }

        unset($data);
    }

    /**
     * Get Result from builder
     *
     * @return array
     */
    public function getResult()
    {
        return array("data" => $this->data, "relations" => $this->relations);
    }
}
