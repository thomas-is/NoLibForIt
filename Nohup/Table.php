<?php

namespace NoLibForIt\Nohup;


/**
  * @requires env NOHUP_PROC
  */

class Table {

  private string $dir;
  private array  $process = [];

  public function __construct() {

    if( empty(shell_exec("which nohup")) ) {
      throw new \Exception(__CLASS__." can't find nohup!");
    }

    $this->dir = getenv("NOHUP_PROC");
    if( ! is_dir($this->dir) ) {
      throw new \Exception(__CLASS__." not a directory: {$this->dir}");
    }

    $pdir = [];
    foreach ( scandir($this->dir) as $dir ) {
      is_numeric($dir) ? $pdir[] = $dir;
    }

    foreach ( $pdir as $id ) {
      $this->proc[ (int) $id ] = new Job( (int) $id );
    }

  }

  private function action( string $action, int $id ) {
    if( empty($this->proc[$id]) ) {
      return null;
    }
    return $this->proc[$id]->$action();
  }

  public function start ( int $id ) { return $this->action("start" , $id); }
  public function cancel( int $id ) { return $this->action("cancel", $id); }
  public function kill  ( int $id ) { return $this->action("kill"  , $id); }

  public function clean() {
    foreach( $this->process as $p ) { $p->clean(); }
  }

  public function state() {

    $top = [];
    foreach ( $this->process as $p ) {
      $top[ $p->id() ] = [
        "state" =>  $p->state(),
        "title" =>  $p->getTitle()
      ];
    }
    return $top;
  }

}

?>
