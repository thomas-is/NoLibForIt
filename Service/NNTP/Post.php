<?php

namespace NoLibForIt\Service\NNTP;

use \NoLibForIt\API\Answer  as Answer;

class Post {

  public static function handle($request) {

    if( $request->method != "POST" ) {
      Answer::json(405,array("error"=>"method not allowed"));
    }

    $lines = explode("\n",$request->body);

    if( empty($lines) ) {
      Answer::json(400,array("error"=>"bad request"));
    }

    $nntp = new \NoLibForIt\NNTP\Client(NNTP_HOST,NNTP_PORT,NNTP_USE_TOR);
    if( empty($nntp) ) {
      Answer::json(500,array("error"=>"NNTP client failed"));
    }
    if( defined('NNTP_USER') && defined('NNTP_PASS') ) {
      $nntp->auth(NNTP_USER,NNTP_PASS);
    }

    if ( ! $nntp->post($lines) ) {
      Answer::json(520,array("status"=>$nntp->status));
    }
    Answer::json(200,array("status"=>$nntp->status));

  }

}

?>
