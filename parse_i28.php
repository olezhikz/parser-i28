<?php

require 'vendor/autoload.php';

use DiDom\Document;
use Olezhik\Parser\ParseCategoriesi28;
use Olezhik\Parser\ParseLinksi28;

$categories = new ParseCategoriesi28;

$aLinks = $categories('http://www.i28.com');

file_put_contents(__DIR__ . '/products.txt','');
file_put_contents(__DIR__ . '/categories.txt', '');

foreach ($aLinks as $sLink) {
    file_put_contents(__DIR__ . '/categories.txt', $sLink.PHP_EOL, FILE_APPEND);
}

foreach ($categories->productsOne as $sLink) {
    file_put_contents(__DIR__ . '/products.txt', $sLink.PHP_EOL, FILE_APPEND);
}

$links = new ParseLinksi28;
$links($aLinks);

echo PHP_EOL;
