<?php declare(strict_types=1);

namespace App\Shell;

class Shell extends \Psy\Shell
{
    protected function getHeader()
    {
        return 'Journal interactive shell';
    }
}
