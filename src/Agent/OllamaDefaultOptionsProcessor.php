<?php

namespace App\Agent;

use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\InputProcessorInterface;

final class OllamaDefaultOptionsProcessor implements InputProcessorInterface
{
    public function processInput(Input $input): void
    {
        $input->setOptions([
            'think' => false,
            'num_predict' => 500,
            ...$input->getOptions(),
        ]);
    }
}
