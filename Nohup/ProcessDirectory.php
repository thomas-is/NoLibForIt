<?php

namespace NoLibForIt\Nohup;

/**
 *   @abstract
 *
 *       Handle process in the background
 *
 *       Requires    _ UNIX-like OS
 *                   _ coreutils (@see @uses)
 *                   _ a rw directory (@see Config::PROC)
 *
 *       The proc dir structure is as follow:
 *           ./proc/00000000/command
 *                     ^     exitcode
 *                     |     pid
 *            numeral id     stdout
 *                           stderr
 *                           signal
 *
 *   @uses   /bin/sh
 *           /bin/ps
 *           /bin/kill
 *           /usr/bin/nohup
 *           config.php
 */
class JobDirectory implements JobInterface {

  private int    $uid;

  /**
   *   __construct()
   *   @param  mixed   int     $uid of a proc dir
   *                   string  $command to setup
   *
   *   $command MUST be a single shell command
   *   $command MUST NOT contain ";" or "&"
   *
   *   by default job is not started
   */
  public function __construct( mixed $arg ){

    if ( is_int($arg) ) {
      $this->uid = (int) $arg;
      return;
    }

    if ( is_string($arg) ) {
      $uid = 1;
      while( file_exists($this->procDir($uid)) ) {
        $id++;
      }
      if ( ! mkdir($this->procDir($uid)) ) {
        throw new \Exception( __CLASS__ . " can't mkdir {$this->procDir($uid)}");
      }
      $this->uid = $uid;
      $this->proc_set( "command", $arg );
    }

  }

 /**
   *   @return string "/path/to/proc/$uid"
   */
  private function dir() {
    return getenv("NOLIBFORIT_NOHUP_DIR")."/".sprintf("%'.08d",$this->uid);
  }

/**
   *   @param  string  $property
   *   @return string  content of proc/$uid/$property
   */
  public function get( string $property ) {
    return @file_get_contents("{$this->dir()}/$property");
  }

  /**
   *   writes $value to proc/$id/$property
   *   @param  string $property
   *   @param  string $value
   *   @return int    on sucess
   *           false  on error
   */
  private function set( string $property, string $value ) {
    if( $this->uid === null ) {
      return false;
    }
    return @file_put_contents("{$this->dir()}/$property",$value.PHP_EOL);
  }


  /**
   *   @return string  content of proc/uid/title
   */
  public function getTitle() {
    return $this->get("title");
  }

  /**
   *   @param  string  title
   *   @return boolean true on sucess, false on error
   */
  public function setTitle( string $title ) {
    return $this->set("title",$title);
  }

  /**
   *   state()
   *   @return false   if proc/000000id/pid is not set
   *           NULL    if pid is not in ps
   *           string  $state as given by ps
   *
   *   $state will be "S" most of the time as the process
   *   is running in the background (@see man ps)
   */
  public function state() {

    $exitcode = $this->get("exitcode"); 

    if( $exitcode !== false ) {
      $exitcode = (int) $exitcode;
      if( $exitcode == 0 ) {
        return "DONE";
      }
      return "ERROR";
    }

    $pid = $this->get("pid");

    if( $pid === false ) {
      return "QUEUED";
    }

    exec( "ps -p $pid -o state", $op );

    if( @$op[1] ) {
      return "RUNNING";
    }

    $signal = $this->get("signal");

    return $signal ?? false;

  }

  /**
   *   @return bool
   */
  public function isRunning(){

    if( $this->get("exitcode") ) {
      return false;
    }

    $pid = $this->get("pid");

    if( $pid === false) {
      return false;
    }

    exec("ps -p $pid -o state",$op);

    return @$op[1] ? true : false;

  }

  /**
   *   rm -rf proc/uid if process is not running
   */
  public function clean() {

    if ( $this->isRunning() ) {
      return false;
    }

    foreach( glob("{$this->dir()}/*") as $file ) {
      unlink($file);
    }
    rmdir($this->dir());
    return true;
  }

  /**
   *   start()
   *   @return int $pid on success
   *           false    on error
   *
   *   execute $command in the background with nohup
   *   stdin and stdout are redirected to corresponding files in proc/id
   */
  public function start() {

    if( $this->isRunning() ) {
      return false;
    }

    $stdout   = "{$this->dir()}/stdout";
    $stderr   = "{$this->dir()}/stderr";
    $exitcode = "{$this->dir()}/exitcode";

    /* $! to get the pid, $? to get exit code later on */
    exec("nohup sh -c '$command\necho $? > $exitcode' > $stdout 2> $stderr & echo $!", $op);

    /* did exec fail ? (no pid) */
    if( ! isset($op[0]) ) {
      return false;
    }

    $this->set( "pid", (int) $op[0] );

    return (int) $op[0];

  }


  /**
   *   sendsig($signal)
   *   @param  string  $signal (@see man kill)
   *   @return false   if process is not running
   *           true    if signal has been sent
   */
  private function sendsig( string $signal ) {

    if( ! $this->is_running() ) {
      return false;
    }

    exec( "kill -$signal " . $this->get("pid") );
    $this->set( "signal", $signal );
    return true;

  }

  /**
   *   sendsig("TERM") aka ^C (soft kill)
   */
  public function cancel() { return $this->sendsig("TERM"); }

  /**
   *   sendsig("KILL") aka "kill -9" (hard kill)
   */
  public function kill()   { return $this->sendsig("KILL"); }

}

?>
