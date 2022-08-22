<?php

namespace CwoodStrib\LaravelConfigDump\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

class EnvCallVisitor extends NodeVisitorAbstract {
  private array $names = [];

  public function getNames(): array {
    return array_values($this->names);
  }

  public function extractName(Node $node): ?string {
    if (isset($node->name) && $node->name instanceof Name) { 
      [$name] = $node->name->parts;
      return $name;
    }

    if (isset($node->name) && $node->name instanceof Variable) {
      return $node->name->name;
    }

    return null; 
  }

  public function enterNode(Node $node) {
      if ($node instanceof FuncCall) {
        $name = $this->extractName($node);

        if (is_null($name)) {
          throw new \Error("Failed extracting name from node: " . get_class($node));
        }

        if ($name === "env") {
          [$envVarArg] = $node->args;
          $envVarString = $envVarArg->value;

          if (!($envVarString instanceof String_)) {
            throw new \Error("Found argument of type " . get_class($envVarString) . ". Analysis currently only supports string literal arguments.");
          }

          $envVarName = $envVarString->value;
          $this->names[] = $envVarName;
        }
      }
    }
}