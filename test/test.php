<?php
require __DIR__ . '/../vendor/autoload.php';

$objet = new \tools\Tools();
$days = (new \tools\Tools())->date()::days_in_month('12','2023');
echo $days;die();