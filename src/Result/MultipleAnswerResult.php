<?php
/* For licensing terms, see LICENSE */

namespace ProcessSurveyPHP\Result;

use ProcessSurveyPHP\Core\Variable;

/**
 * Class MultipleAnswerResult.
 *
 * @package ProcessSurveyPHP\Result
 */
class MultipleAnswerResult
{
    /**
     * @var array|Variable[]
     */
    private $variables;
    /**
     * @var array
     */
    private $dataByVariable;

    /**
     * @var bool
     */
    private $isDichotomy;
    /**
     * @var int
     */
    private $countedValue;

    public function __construct(bool $isDichotomy, int $countedValue)
    {
        $this->isDichotomy = $isDichotomy;
        $this->countedValue = $countedValue;
    }

    /**
     * @param Variable $variable
     *
     * @return $this
     */
    public function addVariable(Variable $variable)
    {
        $this->variables[$variable->getName()] = $variable;

        return $this;
    }

    /**
     * @param string $variableName
     * @param array  $data
     *
     * @return $this
     */
    public function addDataByVariable(string $variableName, array $data)
    {
        $this->dataByVariable[$variableName] = $data;

        return $this;
    }

    /**
     * @return array
     */
    private function calculate(): array
    {
        $results = [];

        foreach ($this->variables as $variable) {
            $variableName = $variable->getName();

            $counts = array_count_values(
                $this->dataByVariable[$variableName]
            );

            if (!$this->isDichotomy) {
                $variableValues = $variable->getValues();

                foreach ($variableValues as $variableValue) {
                    if (!isset($results[$variableValue])) {
                        $results[$variableValue] = 0;
                    }

                    if (isset($counts[$variableValue])) {
                        $results[$variableValue] += $counts[$variableValue];
                    }
                }
            } else {
                if (!in_array($this->countedValue, $variable->getValues())) {
                    continue;
                }

                $variableValues = [$this->countedValue];

                foreach ($variableValues as $variableValue) {
                    if (!isset($results[$variableName])) {
                        $results[$variable->getLabel()] = 0;
                    }

                    $results[$variable->getLabel()] += $counts[$variableValue];
                }
            }
        }

        return $results;
    }

    /**
     * @param int $recordsTotal
     *
     * @return array
     */
    public function process(int $recordsTotal): array
    {
        $results = $this->calculate();
        $resultsTotal = array_sum($results);

        $statistics = array_map(
            function ($result) use ($resultsTotal, $recordsTotal) {
                return [
                    $result,
                    $result / $resultsTotal * 100,
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
                array_sum(array_column($statistics, 2)),
            ],
        ];
    }
}
