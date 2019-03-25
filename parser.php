<?php
/* For licensing terms, see LICENSE */

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('Survey Parser', '0.1a');

$application
    ->addCommands([
        new \SurveyParser\Command\MultipleChoiceCommand(),
        new \SurveyParser\Command\MultipleAnswerCommand(),
    ]);

$application->run();
