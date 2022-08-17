<?php

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;

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

    return "";
  }

  public function enterNode(Node $node) {
      if ($node instanceof FuncCall) {
        $name = $this->extractName($node);

        if (is_null($name)) {
          throw new Error("Failed extracting name from node: " . get_class($node));
        }

        if ($name === "env") {
          [$envVarArg] = $node->args;
          $envVarString = $envVarArg->value;
          $envVarName = $envVarString->value;
          $this->names[] = $envVarName;
        }
      }
    }
}