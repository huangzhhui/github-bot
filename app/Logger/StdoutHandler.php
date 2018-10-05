<?php

namespace App\Logger;


use Swoft\Log\FileHandler;

class StdoutHandler extends FileHandler
{

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        printf('%s', implode("\n", $record) . "\n");
    }

}