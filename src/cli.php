<?php

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/EnvCallVisitor.php");


use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitorAbstract;


$code = file_get_contents(__DIR__ . "/../data/test.php");



// getenv
// env


$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

try {
    $ast = $parser->parse($code);
    $traverser = new NodeTraverser();
    $envCallVisitor = new EnvCallVisitor();
    $traverser->addVisitor($envCallVisitor);
    $traverser->traverse($ast);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    return;
}

