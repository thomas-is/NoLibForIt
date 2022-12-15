<?php

namespace NoLibForIt\Curl;

class Answer {

  public int    $statusCode = 0;
  public array  $header     = [];
  public string $body       = "";

  /**
    * perform the request and parse the answer
    * @throws Exception on HTTP >= 400
    */
  public function __construct( array $options ) {

    $ch = curl_init();

    curl_setopt_array($ch, $options);

    $packet = curl_exec($ch);
    $info   = curl_getinfo($ch);

    curl_close($ch);

    $this->statusCode = $info['http_code'];
    $headerSize       = $info['header_size'];
    $this->header     = self::parseLastHeader(substr($packet,0,$headerSize));
    $this->body       = substr($packet,$headerSize);

  }

  /**
    * decode json body as stdClass
    * @return stdClass
    **/
  public function asObject() {
    return json_decode( $this->body, false );
  }

  /**
    * decode json body as array
    * @return array
    **/
  public function asArray() {
    return json_decode( $this->body, true );
  }

  /**
    * parse last response header
    * @static
    * @param  string  raw HTTP headers
    * @return array   last response header
    */
  private static function parseLastHeader( string $text ) {

    $lines = explode("\n",$text);

    $blocks = [[]];
    $index = 0;
    foreach( $lines as $line) {
      $line = rtrim($line);
      if( empty($line) ) { $index++; continue; }
      $blocks[$index][] = $line;
    }

    $lastBlock = array_pop($blocks);

    $header = [];
    foreach( $lastBlock as $line ) {
      if( stripos($line,"HTTP") === 0 ) {
        $field = explode( "/", $line, 2);
        $field = explode( " ", (string) @$field[1], 3);
        $header['HTTP']['version'] = @$field[0];
        $header['HTTP']['code']    = @$field[1];
        $header['HTTP']['message'] = @$field[2];
      } else {
        $field = explode(": ",$line,2);
        $header[$field[0]] = @$field[1];
      }
    }

    return $header;

  }


