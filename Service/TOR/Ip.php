<?php

namespace NoLibForIt\Service\TOR;

use \NoLibForIt\API\Answer as Answer;
use \NoLibForIt\TOR\TOR as TOR;

class Ip {

  public static function handle($request) {

    $socket = TOR::open("api.ipify.org", 80);

    if ( empty($socket) ) {
      Answer::json(503,array("error"=>"service unavailable"));
    };

    $lines = "";
    fwrite($socket, "GET / HTTP/1.0\r\nHost: api.ipify.org\r\nAccept: */*\r\n\r\n");
    while ( ! feof($socket) ) {
      $lines .= fgets($socket, 1024);
    }
    fclose($socket);
    $lines = explode("\r\n",$lines);
    $ip = $lines[array_key_last($lines)];
    Answer::json(200,array("ip"=>$ip));
  }

}
?>
