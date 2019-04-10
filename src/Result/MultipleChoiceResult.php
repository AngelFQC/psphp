<?php
/* For licensing terms, see LICENSE */

namespace ProcessSurveyPHP\Result;

use ProcessSurveyPHP\Core\Variable;

/**
 * Class MultipleChoiceResult.
 *
 * @package SurveyParser
 */
class MultipleChoiceResult
{
    /**
     * @var Variable
     */
    private $variable;
    /**
     * @var array
     */
    private $dataByVariable;
    /**
     * @var string
     */
    protected $displayText;
    /**
     * @var array
     */
    protected $options;

    /**
     * @return string
     */
    public function getDisplayText(): string
    {
        return $this->displayText;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $resultsData
     *
     * @return array
     */
    public function getResults(array $resultsData): array
    {
        return array_count_values($resultsData);
    }

    /**
     * @param array $results
     *
     * @return array
     */
    public function getPercents(array $results): array
    {
        $resultsCount = array_sum($results);

        return array_map(
            function ($value) use ($resultsCount) {
                return $value / $resultsCount * 100;
            },
            $results
        );
    }

    /**
     * MultipleChoiceResult constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return Variable
     */
    public function getVariable(): Variable
    {
        return $this->variable;
    }

    /**
     * @param Variable $variable
     *
     * @return MultipleChoiceResult
     */
    public function setVariable(Variable $variable): MultipleChoiceResult
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return MultipleChoiceResult
     */
    public function setDataByVariable(array $data): MultipleChoiceResult
    {
        $this->dataByVariable = $data;

        return $this;
    }

    public function calculate(): array
    {
        $results = [];

        foreach ($this->variable->getValues() as $value) {
            if (!isset($results[$value])) {
                $results[$value] = 0;
            }
        }

        $counts = array_count_values($this->dataByVariable);

        foreach ($counts as $key => $count) {
            if (!array_key_exists($key, $this->variable->getValues())) {
                continue;
            }

            $label = $this->variable->getValue($key);

            $results[$label] += $count;
        }

        return $results;
    }

    public function process(int $recordsTotal): array
    {
        $results = $this->calculate();

        $statistics = array_map(
            function ($result) use ($recordsTotal) {
                return [
                    $result,
                    $result / $recordsTotal * 100,
                ];
            },
            $results
        );


        return [
            'rows' => $statistics,
            'totals' => [
                array_sum(array_column($statistics, 0)),
                array_sum(array_column($statistics, 1)),
            ],
        ];
    }
}
