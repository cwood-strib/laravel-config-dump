<?php

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/EnvCallVisitor.php");
require_once(__DIR__ . "/PHPFileIterator.php");

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

[$_, $path] = $argv;

$directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
$filter = new PHPFileIterator($directory);
$iterator = new \RecursiveIteratorIterator($filter);

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

try {
  $allNames = [];
  foreach ($iterator as $entry) {
    $fullPath = $entry->getPath() . "/" . $entry->getFileName();

    // TODO: Error handling 
    $code = file_get_contents($fullPath);

    $ast = $parser->parse($code);

    $traverser = new NodeTraverser();
    $envCallVisitor = new EnvCallVisitor();
    $traverser->addVisitor($envCallVisitor);
    $traverser->traverse($ast);

    $names = $envCallVisitor->getNames();

    // array_merge?
    foreach ($names as $name) {
      $allNames[] = $name;
    }
  }

  $uniqueNames = array_unique($allNames);
  foreach ($uniqueNames as $name) {
    echo $name . "\n";
  }
} catch (Error $error) {
  echo "Parse error: {$error->getMessage()}\n";
  return;
}
