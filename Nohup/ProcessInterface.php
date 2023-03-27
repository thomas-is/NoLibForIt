<?php

namespace NoLibForIt\Nohup;

interface ProcessInterface {

  private int $uid;

  public function __construct( mixed $arg );
  /**
    * @param  string property
    * @return string value
    */
  private function get( string $property );
  /**
    * @param  string property, string value
    * @return bool success
    */
  private function set( string $property, string $value );

  public function getTitle();
  public function setTitle( string $title ) {}

  /**
   *   state()
   *   @return false   pid is not set
   *           NULL    pid is set but not in ps
   *           string  state as given by ps
   *   state will be "S" most of the time as the process
   *   is running in the background (@see man ps)
   */
  public function state();

  /**
   *   @return bool
   */
  public function isRunning();

  /**
   *   unset all not running processes
   */
  public function clean();

  /**
   *   start()
   *   @return int $pid on success
   *           false    on error
   */
  public function start();

  /**
   *   sendsig($signal)
   *   @param  string  $signal (@see man kill)
   *   @return false   if process is not running
   *           true    if signal has been sent
   */
  private function sendsig( string $signal );

  /**
   *   sendsig("TERM") aka ^C (soft kill)
   */
  public function cancel();

  /**
   *   sendsig("KILL") aka "kill -9" (hard kill)
   */
  public function kill();

}

?>
