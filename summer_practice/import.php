<?php

require_once __DIR__ . '/bootstrap.php';

use App\Console\ImportCountriesCommand;

$command = new ImportCountriesCommand();
$command->execute($argv);
