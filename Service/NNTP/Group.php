<?php

namespace NoLibForIt\Service\NNTP;

use \NoLibForIt\API\Answer  as Answer;

class Group {

  public static function handle($request) {

    if( ! defined('API_NNTP_HOST') ) {
      Answer::json(500,array("error"=>"API_NNTP_HOST is undefined"));
    }

    if( $request->method != "GET" ) {
      Answer::json(405,array("error"=>"method not allowed"));
    }

    $name  = @$request->argv[1];
    $first = (int) @$request->argv[2];
    $last  = (int) @$request->argv[3];

    if( empty($name) ) {
      Answer::json(400,array("error"=>"bad request"));
    }

    $nntp = new \NoLibForIt\NNTP\Client(API_NNTP_HOST);
    if( empty($nntp) ) {
      Answer::json(500,array("error"=>"NNTP client failed"));
    }

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
