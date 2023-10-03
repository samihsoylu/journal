<?php

declare(strict_types=1);

test('all classes do not contain debugging functions like dd() or dump()')
    ->expect(['dd', 'dump', 'var_dump', 'var_export', 'print_r'])
    ->not()->toBeUsed();
