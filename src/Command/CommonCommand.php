<?php
/* For licensing terms, see LICENSE */

namespace SurveyParser\Command;

use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommonCommand.
 *
 * @package SurveyParser\Command
 */
abstract class CommonCommand extends Command
{
    /**
     * @var Reader
     */
    protected $dataReader;
    /**
     * @var Reader
     */
    protected $variablesReader;

    protected function configure()
    {
        $this
            ->addOption(
                'data-path',
                'dp',
                InputOption::VALUE_REQUIRED,
                'CSV file path for data.',
                'data.csv'
            )
            ->addOption(
                'variables-path',
                'vp',
                InputOption::VALUE_REQUIRED,
                'CSV file path for variables.',
                'variables.csv'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool|int|null
     * @throws \League\Csv\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataPath = $input->getOption('data-path');
        $variablesPath = $input->getOption('variables-path');

        if (!file_exists($dataPath)) {
            throw new \Exception('Data file not found.');
        }

        $this->dataReader = Reader::createFromPath($dataPath, 'r');
        $this->dataReader->setHeaderOffset(0);

        if (!file_exists($variablesPath)) {
            throw new \Exception('Variables file not found.');
        }

        $this->variablesReader = Reader::createFromPath($variablesPath, 'r');
        $this->variablesReader->setHeaderOffset(0);
    }

    /**
     * @param string $variableName
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function getVariableIndex(string $variableName): int
    {
        /** @var int $columnIndex */
        static $columnIndex;

        if (!empty($columnIndex)) {
            return $columnIndex;
        }

        $dataHeader = $this->dataReader->getHeader();

        $columnIndex = array_search($variableName, $dataHeader);

        if (false === $columnIndex) {
            throw new \Exception("Variable $variableName not found.");
        }

        return $columnIndex;
    }

    /**
     * @param string $variableName
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function getVariable(string $variableName): array
    {
        if (!in_array($variableName, $this->variablesReader->getHeader())) {
            throw new \Exception("Variable $variableName not found.");
        }

        $variableColumn = $this->variablesReader->fetchColumn($variableName);
        $variableColumn = iterator_to_array($variableColumn);

        $variable = array_filter($variableColumn);

        if (empty($variable)) {
            throw new \Exception("Variable $variableName is empty.");
        }

        return $variable;
    }

    /**
     * @param string $variableName
     *
     * @return array
     */
    protected function getDataByVariable(string $variableName): array
    {
        $records = $this->dataReader->getRecords();
        $records = iterator_to_array($records);
        $data = array_column($records, $variableName);

        return $data;
    }

    /**
     * @param int  $variableIndex
     * @param bool $filterNulls
     *
     * @return array
     */
    protected function getDataByIndex(int $variableIndex, bool $filterNulls = false): array
    {
        $dataColumn = iterator_to_array(
            $this->dataReader->fetchColumn($variableIndex)
        );

        if (!$filterNulls) {
            return $dataColumn;
        }

        return array_filter($dataColumn);
    }
}
