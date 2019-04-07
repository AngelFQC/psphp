<?php
/* For licensing terms, see LICENSE */

namespace ProcessSurveyPHP\Core;

/**
 * Class Variable.
 *
 * @package ProcessSurveyPHP\Core
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

        $info = array_filter($info);

        for ($i = 0; $i < count($info); $i++) {
            $this->values[$i + 1] = $info[$i];
        }
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

    public function getValue($key)
    {
        if (!array_key_exists($key, $this->values)) {
            return null;
        }

        return $this->values[$key];
    }
}
