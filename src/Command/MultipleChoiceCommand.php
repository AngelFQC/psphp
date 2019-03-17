<?php
/* For licensing terms, see LICENSE */

namespace SurveyParser\Command;

use SurveyParser\Result\MultipleChoiceResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('variable', InputArgument::REQUIRED, 'Variable to process.');
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

        try {
            $variable = $this->getVariable($variableName);
        } catch (\Exception $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");

            return;
        }

        $data = $this->getDataByVariable($variableName);

        $multipleChoiceResult = new MultipleChoiceResult($variable);

        $results = $multipleChoiceResult->getResults($data);
        $percents = $multipleChoiceResult->getPercents($results);

        $valuesTotal = array_sum($results);
        $percentsTotal = array_sum($percents);

        $styleRight = new TableStyle();
        $styleRight->setPadType(STR_PAD_LEFT);

        $table = new Table($output);
        $table->setHeaders(
            [
                $multipleChoiceResult->getDisplayText(),
                'N',
                '%',
            ]
        );
        $table->setColumnStyle(1, $styleRight);
        $table->setColumnStyle(2, $styleRight);

        foreach ($results as $option => $value) {
            $percent = number_format($percents[$option], 2);

            $table->addRow([$option, $value, $percent]);
        }

        $table->addRow(new TableSeparator());
        $table->addRow(['Total', $valuesTotal, number_format($percentsTotal, 2)]);
        $table->render();
    }
}
