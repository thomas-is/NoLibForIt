<?php

namespace NoLibForIt\Database;

class Connection {

  protected $config   ;
  protected $username ;
  protected $password ;
  private   $pdo      ;

  public function __construct(
    $database = "mydatabase" ,
    $driver   = "mysql"      ,
    $username = "root"       ,
    $password = "root"       ,
    $host     = "127.0.0.1"  ,
    $port     = 3306         ,
  ) {
    $this->username = $username;
    $this->password = $password;
    $this->config   = "{$driver}:host={$host};port={$port};dbname={$database}",
  }

  /**
    * SQL query
    * @param  string  SQL command
    *         array   [optional] associative array of data
    * @return array   associative data
    *
    */
  public function query( string $command, array $arg = [] ) {
    $statement = $this->execute($command, $arg);
    return $statement->columnCount()
      ? $statement->fetchAll(\PDO::FETCH_ASSOC)
      : [];
  }

  /**
    * initialize PDO
    * @return void
    */
  private function init() {
    if( $this->pdo ) { return; }
    $this->pdo = new \PDO(
      "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database}",
      $this->username,
      $this->password
    );
    $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }

  /**
    * execute SQL query
    * @return PDOStatement
    */
  private function execute( string $command, array $arg = [] ) {
    $this->init();
    $statement = $this->pdo->prepare($command);
    if ( ! empty($arg) ) {
      foreach( $arg as $key => &$value ) {
        $statement->bindParam(":$key", $value);
      }
    }
    $statement->execute($data);
    return $statement;
  }



}

?>
