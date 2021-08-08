<?php

namespace NoLibForIt\Service;

use \NoLibForIt\API\Answer as Answer;

class Ping {

  public static function handle($request) {
    Answer::json(200,$request);
  }

}

?>
