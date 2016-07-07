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
            OR !$source instanceOf ModelInterface) {
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

        if ($this->src instanceOf Resultset) {
            $this->iterate();
            return;
        }

        if ($this->src instanceOf ModelInterface) {
            $this->build($this->src, false);
            return;
        }
    }

    private function iterate()
    {
        foreach ($this->src as $src) {
            /** @type ModelInterface $src */
            $this->build($src);
        }
    }

    /**
     * Build The source
     *
     * @param ModelInterface    $src
     * @param boolean           $multiple
     *
     * @return void
     */
    private function build(ModelInterface $src, $multiple=true)
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
        return array("data" => $data, "relations" => $relations);
    }
}
