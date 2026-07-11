#!/usr/bin/env php
<?php

require_once __DIR__ . '/bootstrap.php';

use App\Console\CleanHistoryCommand;

$args = array_slice($argv, 1);
$command = new CleanHistoryCommand();
$command->execute($args);
