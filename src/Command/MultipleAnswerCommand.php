<?php
/* For licensing terms, see LICENSE */

namespace ProcessSurveyPHP\Command;

use ProcessSurveyPHP\Result\MultipleAnswerResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MultipleAnswerCommand.
 *
 * @package ProcessSurveyPHP\Command
 */
class MultipleAnswerCommand extends CommonCommand
{
    protected static $defaultName = "multiple_answer";

    /** @var MultipleAnswerResult */
    private $result;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Parse a multiple answer question.')
            ->setHelp('This command allows you to parse a multiple answer question.')
            ->addArgument('variables', InputArgument::IS_ARRAY, 'Variable names to process.')
            ->addOption('label', 'l', InputOption::VALUE_REQUIRED, 'Label for this group of variable.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $variableNames = $input->getArgument('variables');
        $label = $input->getOption('label');

        try {
            if (empty($variableNames)) {
                throw new \Exception('Please enter variable names to process.');
            }

            parent::execute($input, $output);

            foreach ($variableNames as $variableName) {
                if (array_key_exists($variableName, $this->variables)) {
                    continue;
                }

                throw new \Exception('Variable "'.$variableName.'" not found.');
            }
        } catch (\Exception $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");

            return;
        }

        $this->result = new MultipleAnswerResult();

        foreach ($variableNames as $variableName) {
            $this
                ->result
                ->addVariable($this->variables[$variableName])
                ->addDataByVariable(
                    $variableName,
                    $this->getDataByVariable($variableName)
                );
        }

        $statistics = $this->result->process(
            $this->dataReader->count()
        );

        $styleRight = new TableStyle();
        $styleRight->setPadType(STR_PAD_LEFT);

        $table = new Table($output);
        $table->setHeaders([$label, 'N', '%', 'Cases %']);

        foreach ($statistics['rows'] as $option => $cols) {
            $table->addRow(
                [
                    $option,
                    $cols[0],
                    number_format($cols[1], 2),
                    number_format($cols[2], 2),
                ]
            );
        }

        $table->addRow(new TableSeparator());
        $table->addRow([
            'Total',
            $statistics['totals'][0],
            number_format($statistics['totals'][1], 2),
            number_format($statistics['totals'][2], 2),
        ]);
        $table->setColumnStyle(1, $styleRight);
        $table->setColumnStyle(2, $styleRight);
        $table->setColumnStyle(3, $styleRight);
        $table->setStyle('box');
        $table->render();
    }
}
