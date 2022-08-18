<?php

require_once(__DIR__ . "/../vendor/autoload.php");

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use CwoodStrib\LaravelConfigDump\Visitors\EnvCallVisitor;
use CwoodStrib\LaravelConfigDump\PHPFileIterator;
use CwoodStrib\LaravelConfigDump\Commands\{Config, Env, Command};
use CwoodStrib\LaravelConfigDump\MatchValue;
use CwoodStrib\LaravelConfigDump\Visitors\ConfigCallVisitor;

if (count($argv) < 3) {
  echo "Must provide command and path to Laravel Project";
  exit(1);
}

[$_, $commandName, $projectPath] = $argv;

// TODO: string is a very loose type for this
function makeIterator(string $path): RecursiveIteratorIterator {
  $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
  $filter = new PHPFileIterator($directory);
  return new \RecursiveIteratorIterator($filter);
}

function makeCommand(string $name, string $projectPath): ?Command {
  switch ($name) {
    case "config": 
      $iterator = makeIterator($projectPath);
      $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
      $traverser = new NodeTraverser();
      $configCallVisitor = new ConfigCallVisitor();
      $traverser->addVisitor($configCallVisitor);
      return new Config($iterator, $parser, $traverser, $configCallVisitor);
    case "env": 
      $iterator = makeIterator($projectPath);
      $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
      $traverser = new NodeTraverser();
      $envCallVisitor = new EnvCallVisitor();
      $traverser->addVisitor($envCallVisitor);
      return new Env($iterator, $parser, $traverser, $envCallVisitor);
  }
}

$cmd = makeCommand($commandName, $projectPath);

if (is_null($cmd)) {
  echo "Invalid command provided: " . $commandName;
  exit(1); 
}

$results = $cmd->execute()->getOutput();

// TODO: Re-think output 
if ($cmd instanceof Config) {
  foreach ($results as $result) {
    echo $result->getKey() . " | " . $result->getValue() . " | " . $result->getType() . "\n";
  }
} else {
  foreach ($results as $result) {
    echo $result . "\n";
  }
}
