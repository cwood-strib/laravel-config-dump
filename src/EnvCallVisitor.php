<?php

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class EnvCallVisitor extends NodeVisitorAbstract {
  public function enterNode(Node $node) {
      var_dump($node);
      // if ($node instanceof Function_) {
          // Clean out the function body
          // $node->stmts = [];
      // }
  }
}