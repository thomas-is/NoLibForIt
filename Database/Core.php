<?php

namespace NoLibForIt\Database;

/**
  *   PDO is created only if a query is made
  *   Connection is then reused if needed
  */
abstract class Core {

  protected string $driver   ;
  protected string $host     ;
  protected int    $port     ;
  protected string $database ;
  protected string $username ;
  protected string $password ;

  private $pdo;
  private $config;

  private init() {
    $this->config =  $this->driver        . ":"
      . "host="    . $this->host          . ";"
      . "port="    . (string) $this->port . ";"
      . "dbname="  . $this->database;
    $this->pdo = new \PDO( $this->config, $this->username, $this->password );
    $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }

  private function execute( string $command, array $param = [] ) {
    if( ! $this->pdo ) {
      $this->init();
    }
    $statement = $this->pdo->prepare($command);
    foreach ($param as $key => &$value) {
      $statement->bindParam(":$key", $value);
    }
    $statement->execute($data);
    return $statement;
  }

  /**
    *   SQL query
    *   @param  @string SQL command
    *           @array  [optional] associative array of parameters
    *   @return @array  associative
    */
  public function query( string $command, array $param = [] ): array {
    $statement = $this->execute( $command, $param );
    return $statement->columnCount() == 0
      ? []
      : $statement->fetchAll(\PDO::FETCH_ASSOC);
  }

}

?>
