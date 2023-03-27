<?php

namespace NoLibForIt\Database;

class Connection extends Core {

  public function __construct(
    string $driver   ,
    string $host     ,
    int    $port     ,
    string $database ,
    string $username ,
    string $password ,
  ) {
    $this->driver   = $driver   ;
    $this->host     = $host     ;
    $this->port     = $port     ;
    $this->database = $database ;
    $this->username = $username ;
    $this->password = $password ;
  }

}

?>
