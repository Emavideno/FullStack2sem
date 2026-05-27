<?php

require_once 'ArrayWrapper.php';

$obj = new ArrayWrapper([
    'name' => 'Иван',
    'age' => 25
]);

echo $obj->name . "<br>";

$obj->city = 'Москва';
echo $obj->city . "<br>";

var_dump(isset($obj->age));
unset($obj->age);
var_dump(isset($obj->age));

echo $obj . "<br>";

echo $obj('name') . "<br>";

print_r($obj());

$copy = clone $obj;
$copy->name = 'Петр';

echo $obj->name . "<br>";
echo $copy->name . "<br>";
