<?php

namespace CwoodStrib\LaravelConfigDump\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;

class ConfigCallVisitor extends NodeVisitorAbstract
{
  private array $names = [];
  public int $errorCount = 0;

  public function getNames(): array
  {
    return array_unique(array_values($this->names));
  }

  public function extractFunctionName(Node $node): ?string
  {
    if (isset($node->name) && $node->name instanceof Name) {
      [$name] = $node->name->parts;
      return $name;
    }

    if (isset($node->name) && $node->name instanceof Variable) {
      return $node->name->name;
    }

    return null;
  }

  public function extractConfigArgName($node): ?string {
    if (isset($node->args) && !empty($node->args)) {
      [$firstArg] = $node->args;

      // Note: It's possible that the input for a config call is
      // a variable -- how to handle that? Or just warn? 

      if (isset($firstArg->value) && $firstArg->value instanceof String_) {
        return $firstArg->value->value;
      }
    }

    return null;
  }

  public function enterNode(Node $node)
  {

    // Facade variation
    if ($node instanceof StaticCall) {
      if ($node->class instanceof FullyQualified) {
        $lastPart = array_pop($node->class->parts);
        if ($lastPart === "Config") {
          $name = $this->extractConfigArgName($node);
          if (is_null($name)) {
            throw new \Error("Failed extracting arg name from node: " . get_class($node)); 
          }
          $this->names[] = $name;
        }
      }  
    }


    // Global function call
    if ($node instanceof FuncCall) {
      $fnName = $this->extractFunctionName($node);

      if (is_null($fnName)) {
        throw new \Error("Failed extracting function name from node: " . get_class($node));
      }

      if ($fnName === "config") {
        $name = $this->extractConfigArgName($node);
        if (is_null($name)) {
          // throw new \Error("Failed extracting arg name from node: " . get_class($node)); 
          $this->errorCount++;
        }
        $this->names[] = $name;
      }
    }
  }
}
