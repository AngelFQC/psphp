<?php
/* For licensing terms, see LICENSE */

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('Survey Parser', '0.1a');

$application
    ->addCommands([
        new \ProcessSurveyPHP\Command\MultipleChoiceCommand(),
        new \ProcessSurveyPHP\Command\MultipleAnswerCommand(),
    ]);

$application->run();
