#!/usr/bin/env php
<?php

require_once __DIR__ . '/bootstrap.php';

use App\Console\CreateAdminCommand;

$args = array_slice($argv, 1);
$command = new CreateAdminCommand();
$command->execute($args);
