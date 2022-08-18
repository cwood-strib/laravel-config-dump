<?php


namespace CwoodStrib\LaravelConfigDump;

class MatchValue {
  const ENV_KEY = "ENV_KEY";
  const LITERAL = "LITERAL";
  const DYNAMIC = "DYNAMIC";

  private string $type;
  private string $key;
  private string $filePath;
  private $value;

  private function __construct(string $key, string $type, $value, $filePath)
  {
    $this->type = $type; 
    $this->key = $key; 
    $this->value = $value; 
    $this->filePath = $filePath;
  } 

  public function getFilePath() {
    return $this->filePath;
  }

  public function getKey() {
    return $this->key;
  }

  public function getValue() {
    if ($this->type === self::DYNAMIC) {
      if (!is_null($this->value)) {
        return get_class($this->value);
      }
      return "";
    }
    return $this->value;
  }

  public function getType() {
    return $this->type;
  }

  static public function fromEnvCall(string $key, $value, string $filePath) {
    return new self($key, MatchValue::ENV_KEY, $value, $filePath);
  }

  static public function fromLiteral(string $key, $value, string $filePath) {
    return new self($key, MatchValue::LITERAL, $value, $filePath);
  }

  static public function fromDynamic(string $key, $value, string $filePath) {
    return new self($key, MatchValue::DYNAMIC, $value, $filePath);
  }
}