#!/usr/bin/env php
<?php

require_once __DIR__ . '/bootstrap.php';

use App\Console\GenerateQuestionsCommand;

$command = new GenerateQuestionsCommand();
$command->execute();
