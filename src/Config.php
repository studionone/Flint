<?php
namespace Flint;

use Flint\Exception\InvalidFileException;

class Config
{
    use Singleton;

    public function load($filename)
    {
        if (! file_exists($filename)) {
            throw new InvalidFileException('Config can\'t be loaded, file does not exist: '.$filename);
        }

        ob_start();
        $result = require $filename;
        ob_end_clean();

        return $result;
    }
}
