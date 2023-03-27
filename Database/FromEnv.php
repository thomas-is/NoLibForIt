<?php

namespace NoLibForIt\Database;

class FromEnv extends Core {

  public function __construct( string $prefix = "DB" ) {
    $this->driver   =       getenv("{$prefix}_DRIVER")  ;
    $this->host     =       getenv("{$prefix}_HOST")    ;
    $this->port     = (int) getenv("{$prefix}_PORT")    ;
    $this->database =       getenv("{$prefix}_DATABASE");
    $this->username =       getenv("{$prefix}_USERNAME");
    $this->password =       getenv("{$prefix}_PASSWORD");
  }

}

?>
