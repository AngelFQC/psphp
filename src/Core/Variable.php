<?php
/* For licensing terms, see LICENSE */

namespace SurveyParser\Core;

/**
 * Class Variable.
 *
 * @package SurveyParser\Core
 */
class Variable
{
    private $type;
    private $name;
    private $label;
    private $values;
    private $scale;

    /**
     * Variable constructor.
     *
     * @param string     $name
     * @param \Generator $info
     */
    public function __construct(string $name, \Generator $info)
    {
        $info = iterator_to_array($info);

        $this->name = $name;
        $this->label = array_shift($info);
        $this->values = array_filter($info);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
