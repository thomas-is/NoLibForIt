<?php

namespace NoLibForIt\Service;

use \NoLibForIt\API\Answer as Answer;

class DumpServer {

  public static function handle() {
    Answer::json(200,$_SERVER);
  }

}

?>
