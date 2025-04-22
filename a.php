<?php

$rowsMap = [1 => 2];

$a = preg_replace_callback(
    '/(?<=Ошибка в строке )(\d+)/ui',
    fn($m) => $rowsMap[$m[0]] ?? $m[0],
    'Ошибка в строке 1'
);

$b = 1;
