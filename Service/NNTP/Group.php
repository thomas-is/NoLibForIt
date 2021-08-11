<?php

namespace NoLibForIt\Service\NNTP;

use \NoLibForIt\API\Answer  as Answer;

class Group {

  public static function handle($request) {

    if( $request->method != "GET" ) {
      Answer::json(405,array("error"=>"method not allowed"));
    }

    $name  = @$request->argv[1];
    $first = (int) @$request->argv[2];
    $last  = (int) @$request->argv[3];

    if( empty($name) ) {
      Answer::json(400,array("error"=>"bad request"));
    }

    $nntp = new \NoLibForIt\NNTP\Client(NNTP_HOST,NNTP_PORT,NNTP_USE_TOR);
    if( empty($nntp) ) {
      Answer::json(500,array("error"=>"NNTP client failed"));
    }

//    if( defined('NNTP_USER') && defined('NNTP_PASS') ) {
//      $nntp->auth(NNTP_USER,NNTP_PASS);
//    }

    $group = new \NoLibForIt\NNTP\Group($name,$nntp);
    if( $nntp->status->code != 211 ) {
      Answer::json(520,array("status"=>$nntp->status));
    }

    if( empty($first) ) {
      Answer::json(200,array("group"=>$group));
    }

    $overview = $group->xover($first, $last);
    if( $nntp->status->code != 224 ) {
      Answer::json(520,array("status"=>$nntp->status));
    }

    Answer::json(200,array("group"=>$group));

  }

}

?>
