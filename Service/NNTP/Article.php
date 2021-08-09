<?php

namespace NoLibForIt\Service\NNTP;

use \NoLibForIt\API\Answer  as Answer;

class Article {

  public static function handle($request) {

    if( ! defined('API_NNTP_HOST') ) {
      Answer::json(500,array("error"=>"API_NNTP_HOST is undefined"));
    }

    if( $request->method != "GET" ) {
      Answer::json(405,array("error"=>"method not allowed"));
    }

    $mid  = @$request->argv[1];

    if( empty($mid) ) {
      Answer::json(400,array("error"=>"bad request"));
    }

    $nntp = new \NoLibForIt\NNTP\Client(API_NNTP_HOST);
    if( empty($nntp) ) {
      Answer::json(500,array("error"=>"NNTP client failed"));
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
