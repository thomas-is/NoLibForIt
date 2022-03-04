<?php

namespace NoLibForIt\Database;

abstract class Core {

  protected $driver   ;
  protected $host     ;
  protected $port     ;
  protected $database ;
  protected $username ;
  protected $password ;

  private   $_pdo     ;

  /**
    * You must extend this class with its own __construct()
    **/
  public function __construct() {
    $this->driver   = "mysql"    ;
    $this->host     = "127.0.0.1";
    $this->port     = 3306       ;
    $this->username = "root"     ;
    $this->password = "root"     ;
    $this->database = "mysql"    ;
  }

  /**
    * You should extend this class with its own handleException()
    **/
  protected function handleException( \PDOException $e ) {
    error_log( get_class($this) . " " . $e->getMessage() );
  }

  /**
    * query($command,$arg)
    *   @param  @string SQL command
    *           @array  [optional] associative array of data
    *
    *   @return @array  associative data
    *
    **/
  public function query( string $command, array $arg = [] ) {

    $statement = $this->execute($command, $arg);

    if ( $statement->columnCount() == 0 ) {
      return array();
    }

    return $statement->fetchAll(\PDO::FETCH_ASSOC);

  }


  private function execute( string $command, array $arg = [] ) {

    $this->init();

    try {
      $statement = $this->_pdo->prepare($command);
      if ( ! empty($arg) ) {
        foreach( $arg as $key => &$value ) {
          $statement->bindParam(":$key", $value);
        }
      }
      $statement->execute($data);
      return $statement;
    }

    catch( \PDOException $e ) { $this->handleException($e); }

  }


  private function init() {

    if( $this->_pdo ) { return; }

    try {
      $this->_pdo = new \PDO( "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database}", $this->username, $this->password );
      $this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
      $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    catch( \PDOException $e ) { $this->handleException($e); }

  }


}

?>
