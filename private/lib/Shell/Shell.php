<?php

declare(strict_types=1);

namespace App\Shell;

final class Shell extends \Psy\Shell
{
    protected function getHeader()
    {
        return 'Journal interactive shell';
    }
}
