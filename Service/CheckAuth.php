<?php

namespace NoLibForIt\Service;

use \NoLibForIt\API\Answer as Answer;

class CheckAuth {

  public static function handle($request) {
    $request->requireAuth();
    Answer::json(200,array("message"=>"Access granted"));
  }

}

?>
