<?php

require 'vendor/autoload.php';

use DiDom\Document;
use Olezhik\Parser\ParseProductsi28;

$products = new ParseProductsi28;

$aLinks = $products(__DIR__ . '/products.txt', __DIR__ . '/base.csv');


echo PHP_EOL;
