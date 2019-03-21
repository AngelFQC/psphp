<?php
/* For licensing terms, see LICENSE */

namespace SurveyParser\Command;

use SurveyParser\Result\MultipleChoiceResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MultipleChoiceCommand.
 *
 * @package SurveyParser\Command
 */
class MultipleChoiceCommand extends CommonCommand
{
    protected static $defaultName = "multiple_choice";

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Parse a multiple choice question.')
            ->setHelp('This command allows you to parse a multiple choice question.')
            ->addArgument('variable', InputArgument::REQUIRED, 'Variable to process.')
            ->addOption('other-name', 'o', InputOption::VALUE_OPTIONAL, 'Name for the option Other.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!parent::execute($input, $output)) {
            return;
        };

        $variableName = $input->getArgument('variable');
        $otherName = $input->getOption('other-name');

        try {
            $variable = $this->getVariable($variableName);
            $variableIndex = $this->getVariableIndex($variableName);

            $data = $this->getDataByVariable($variableName);

            if (!empty($otherName)) {
                $otherData = $this->getDataByIndex($variableIndex + 1);
                $otherResults = $this->generateOtherResults($otherName, $variable, $otherData);
            }
        } catch (\Exception $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");

            return;
        }

        $results = $this->generateResults($variable, $data);

        $styleRight = new TableStyle();
        $styleRight->setPadType(STR_PAD_LEFT);

        $table = new Table($output);
        $table->setHeaders($results['header']);
        $table->setRows($results['rows']);
        $table->addRow(new TableSeparator());
        $table->addRow($results['footer']);
        $table->setStyle('box');
        $table->setColumnStyle(1, $styleRight);
        $table->setColumnStyle(2, $styleRight);
        $table->render();

        if (!empty($otherResults)) {
            $table = new Table($output);
            $table->setHeaders($otherResults['header']);
            $table->setRows($otherResults['rows']);
            $table->addRow(new TableSeparator());
            $table->addRow($otherResults['footer']);
            $table->setColumnStyle(1, $styleRight);
            $table->setColumnStyle(2, $styleRight);
            $table->setColumnStyle(3, $styleRight);
            $table->setStyle('box');
            $table->render();
        }
    }

    /**
     * @param array $variable
     * @param array $data
     *
     * @return array
     */
    private function generateResults(array $variable, array $data): array
    {
        $multipleChoiceResult = new MultipleChoiceResult($variable);

        $results = $multipleChoiceResult->getResults($data);
        $percentages = $multipleChoiceResult->getPercents($results);

        $rows = [];

        foreach ($results as $option => $count) {
            $rows[] = [
                $option,
                $count,
                number_format($percentages[$option], 2),
            ];
        }

        return [
            'header' => [
                $multipleChoiceResult->getDisplayText(),
                'N',
                '%'
            ],
            'rows' => $rows,
            'footer' => [
                'Total',
                array_sum($results),
                number_format(array_sum($percentages), 2)
            ],
        ];
    }

    /**
     * @param string $name
     * @param array  $variable
     * @param array  $otherData
     *
     * @throws \Exception
     *
     * @return array
     */
    private function generateOtherResults(string $name, array $variable, array $otherData): array
    {
        $multipleChoiceResult = new MultipleChoiceResult($variable);

        if (!in_array($name, $multipleChoiceResult->getOptions())) {
            throw new \Exception("Option \"$name\" not found.");
        }

        $results = $multipleChoiceResult->getResults($otherData);
        $percentages = $multipleChoiceResult->getPercents($results);

        $callback = function ($key) {
            return !empty($key);
        };

        $validResults = array_filter($results, $callback, ARRAY_FILTER_USE_KEY);
        $validPercentages = $multipleChoiceResult->getPercents($validResults);
        $casesPercentages = array_filter($percentages, $callback, ARRAY_FILTER_USE_KEY);

        $rows = [];

        foreach ($validResults as $option => $count) {
            $rows[] = [
                $option,
                $count,
                number_format($validPercentages[$option], 2),
                number_format($casesPercentages[$option], 2),
            ];
        }

        return [
            'header' => [$name, 'N', '%', 'Cases %'],
            'rows' => $rows,
            'footer' => [
                'Total',
                array_sum($validResults),
                number_format(array_sum($validPercentages), 2),
                number_format(array_sum($casesPercentages), 2),
            ],
        ];
    }
}
