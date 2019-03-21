<?php
/* For licensing terms, see LICENSE */

namespace SurveyParser\Result;

/**
 * Class MultipleChoiceResult.
 *
 * @package SurveyParser
 */
class MultipleChoiceResult
{
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
     *
     * @param array $variableData
     */
    public function __construct(array $variableData)
    {
        $this->displayText = array_shift($variableData);
        $this->options = $variableData;
    }
}
