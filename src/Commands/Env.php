<?php

namespace CwoodStrib\LaravelConfigDump\Commands;

use CwoodStrib\LaravelConfigDump\Visitors\EnvCallVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;

class Env implements Command
{
  private \RecursiveIteratorIterator $iterator;
  private Parser $parser;
  private NodeTraverser $traverser;

  public function __construct(
    \RecursiveIteratorIterator $iterator, 
    Parser $parser, 
    NodeTraverser $traverser, 
    EnvCallVisitor $visitor)
  {
    $this->iterator = $iterator;
    $this->parser = $parser;
    $this->traverser = $traverser;
    $this->visitor = $visitor;
  }

  public function execute() {
    try {
      foreach ($this->iterator as $entry) {
        $fullPath = $entry->getPath() . "/" . $entry->getFileName();

        // TODO: Error handling 
        $code = file_get_contents($fullPath);

        $ast = $this->parser->parse($code);
        $this->traverser->traverse($ast);
      }
    } catch (\Error $error) {
      echo "Parse error: {$error->getMessage()}\n";
      return $this;
    }

    return $this;
  }

  public function getOutput(): array {
    return array_unique(array_values($this->visitor->getNames()));
  }
}
