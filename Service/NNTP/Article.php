<?php

namespace NoLibForIt\Service\NNTP;

use \NoLibForIt\API\Answer  as Answer;

class Article {

  public static function handle($request) {

    if( $request->method != "GET" ) {
      Answer::json(405,array("error"=>"method not allowed"));
    }

    $mid  = @$request->argv[1];

    if( empty($mid) ) {
      Answer::json(400,array("error"=>"bad request"));
    }

    $nntp = new \NoLibForIt\NNTP\Client(NNTP_HOST,NNTP_PORT,NNTP_USE_TOR);
    if( empty($nntp) ) {
      Answer::json(500,array("error"=>"NNTP client failed"));
    }
    if( defined('NNTP_USER') && defined('NNTP_PASS') ) {
      $nntp->auth(NNTP_USER,NNTP_PASS);
    }

    $nntp->article($mid);
    if( $nntp->status->code != 220 ) {
      Answer::json(520,array("status"=>$nntp->status));
    }

    $article = new \NoLibForIt\NNTP\Article($nntp->lines);
    Answer::json(200,$article);

  }

}

?>
