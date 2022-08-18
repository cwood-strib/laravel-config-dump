<?php

namespace CwoodStrib\LaravelConfigDump;

class PHPFileIterator extends \RecursiveFilterIterator
{
  public function accept(): bool
  {
    if ($this->current()->isFile() && strstr($this->current()->getFileName(), ".php") !== false) {
      return true;
    }

    $ignoredDirs = [".", "..", "vendor"];
    if (
      $this->current()->isDir()
      && !in_array($this->current()->getFileName(), $ignoredDirs)
    ) {
      return true;
    }

    return false;
  }
}
