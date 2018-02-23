<?php

namespace Shelf\ModuleManager\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Shelf\ModuleManager\Helper\Data as DataHelper;

abstract class BaseCommand extends Command
{
    const MAX_ATTEMPTS = 3;
    const SLEEP_PROGRESSBAR = 200000;
    const SYMBOL_SUCCESS = '<fg=green;options=bold>✓</>';
    const SYMBOL_ERROR = '<fg=red;options=bold>✗</>';

    /**
     * @var dataHelper
     */
    private $dataHelper;

    public function __construct(
        DataHelper $dataHelper,
        $name = null
    )
    {
        $this->dataHelper = $dataHelper;
        parent::__construct($name);
    }

    /**
     * @param string $info
     * @param string $comment
     * @param array $autoComplete
     * @return Question
     */
    protected function getNormalQuestionString($info, $comment, $autoComplete = [])
    {
        $question = new Question('<info>' . $info . '</info> <comment>[' . $comment . ']</comment>: ');
        $question->setAutocompleterValues($autoComplete);
        $question->setValidator(function ($answer) use ($info) {
            if (! is_string($answer) || $answer == '') {
                throw new \RuntimeException($info . ' is required!');
            }
            return $answer;
        });

        $question->setMaxAttempts(self::MAX_ATTEMPTS);

        return $question;
    }

    /**
     * @param string $info
     * @param string $comment
     * @param array $autoComplete
     * @return Question
     */
    protected function getQuestionToCamelCase($info, $comment, $autoComplete = [])
    {
        $question = $this->getNormalQuestionString($info, $comment, $autoComplete);
        $question->setNormalizer(function ($value) {
            if ($value) {
                $value = $this->dataHelper->normalizeCamelcase($value);
            }

            return $value;
        });

        return $question;
    }

}