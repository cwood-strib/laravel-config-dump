<?php

namespace CwoodStrib\LaravelConfigDump\Commands;

use CwoodStrib\LaravelConfigDump\MatchValue;
use CwoodStrib\LaravelConfigDump\Visitors\ConfigCallVisitor;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;

class Config implements Command {
  private string $rootDir;
  private Parser $parser;
  private $values = [];

  public function __construct(
    string $rootDir,
    Parser $parser
  ) {
    $this->rootDir = $rootDir;
    $this->parser = $parser;
  }

  public function execute() {
    $path = $this->rootDir;

    $iterator = new \RecursiveDirectoryIterator($path);

    foreach ($iterator as $entry) {
      if ($entry->isFile()) {
        $fullPath = $entry->getPath() . "/" . $entry->getFileName();

        $code = file_get_contents($fullPath);
        $ast = $this->parser->parse($code);

        // TODO: Handle more safely 
        // This is the root array
        if ($ast[0] instanceof Return_) {
          $array = $ast[0]->expr;
          $key = str_replace(".php", "", $entry->getBasename());
          $this->parseConfigAst($key, $array, $fullPath);
        }
      }
  }

    return $this;
  }

  public function extractFunctionName($node): ?string {
    if (isset($node->name) && $node->name instanceof Name) { 
      [$name] = $node->name->parts;
      return $name;
    }

    if (isset($node->name) && $node->name instanceof Variable) {
      return $node->name->name;
    }

    return null; 
  }

  public function unwrapKey($node) {
    if ($node instanceof String_) {
      return $node->value;
    } else {
      if ($node instanceof ArrayItem && isset($node->key)) {
        return $node->key->value;
      }
    }

    return null;
  }

  public function makeMatch(string $key, $node, $filePath): MatchValue {
    // TODO: Broad refactor to unpack traverse expressions to try to find env calls

    switch (get_class($node)) {
      case 'PhpParser\Node\Scalar\String_':
        return MatchValue::fromLiteral($key, $node->value, $filePath);
      case 'PhpParser\Node\Expr\Ternary':
        // TODO: Unpack ternary to see if it includes function call on either side
        return MatchValue::fromDynamic($key, $node, $filePath);
      case 'PhpParser\Node\Expr\ConstFetch':
        // TODO: analyse if we can get literal value 
        return MatchValue::fromDynamic($key, $node, $filePath); 
      case 'PhpParser\Node\Expr\UnaryMinus': 
        return MatchValue::fromLiteral($key, "-" . $node->expr->value, $filePath);
      case 'PhpParser\Node\Expr\FuncCall': 
        $name = $this->extractFunctionName($node);

        if (is_null($name)) {
          throw new \Error("Failed extracting name from node: " . get_class($node));
        }

        if ($name === "env") {
          [$envVarArg] = $node->args;
          $envVarString = $envVarArg->value;
          $envVarName = $envVarString->value;
          return MatchValue::fromEnvCall($key, $envVarName, $filePath);
        }
      case 'PhpParser\Node\Expr\ClassConstFetch':
        // var_dump('class const fetch', $node);
        return MatchValue::fromDynamic($key, $node, $filePath);
      case 'PhpParser\Node\Expr\BinaryOp\Concat':
        // TODO: Unpack if the operation has a function call as part of it 
        // Left
        // Right
        return MatchValue::fromDynamic($key, $node, $filePath);
      case 'PhpParser\Node\Scalar\LNumber':
        return MatchValue::fromLiteral($key, $node->value, $filePath);
      case 'PhpParser\Node\Expr\New_':
        return MatchValue::fromDynamic($key, $node, $filePath);
      default: 
        var_dump($node);
        throw new \Error("Cannot unwrap value of $key of type" . get_class($node));
        break;
      }
  }

  public function parseConfigAst($key, $array, string $fileName) {
    $items = $array->items;
    foreach($items as $item) {
      // BFS and filter to only env-backed  
      if ($item->value instanceof Array_) {
        $itemKey = $this->unwrapKey($item, $fileName);
        $fullKey = !empty($key) ? implode(".", [$key, $itemKey]) : $itemKey;
        $this->parseConfigAst($fullKey, $item->value, $fileName);
      } else {
        if (is_null($item)) {
          continue;
        }
        // Decide if the value is a 
        $itemKey = $this->unwrapKey($item->key, $fileName) ?? $key;
        $fullKey = !empty($key) ? implode(".", [$key, $itemKey]) : $itemKey;
        $value = $this->makeMatch($fullKey, $item->value, $fileName);
        $this->values[] = $value;
        $this->uniqueReturnTypes[] = get_class($item->value);
      } 
    }
  }

  public function getOutput(): array {
    return $this->values; 
  }

}