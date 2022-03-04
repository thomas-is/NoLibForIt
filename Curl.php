<?php

namespace NoLibForIt;

class Curl {

  protected $queryParam = [];
  protected $options    = [];

  protected $statusCode = 0;
  protected $body       = "";
  protected $header     = [];

  public function dd() {
    header("Content-Type: application/json");
    echo json_encode([
      "request" => [
        "method"     => @$this->options[CURLOPT_CUSTOMREQUEST],
        "url"        => @$this->options[CURLOPT_URL],
        "header"     => @$this->options[CURLOPT_HTTPHEADER],
        "body"       => @$this->options[CURLOPT_POSTFIELDS],
      ],
      "answer" => [
        "statusCode" => $this->statusCode,
        "header"     => $this->header,
        "body"       => $this->body,
      ],
    ]);
    die();
  }

  /**
    * @param  string $url
    * @return $this
    * set CURLOPT_URL (location)
    **/
  public function to( string $url ) {
    $this->options[CURLOPT_URL] = $url;
    return $this;
  }


  /**
    * set query params from an array (associactive or not)
    * @param  array $params
    * @return $this
    **/
  public function withParams( array $params ) {
    $this->queryParam = [];
    foreach( $params as $key => $value ) {
      if( gettype($key) == "integer" ) {
        $this->queryParam[$value] = null;
      } else {
        $this->queryParam[$key] = $value;
      }
    }
    return $this;
  }

  /**
    * set single query param
    * @param  array $key, [mixed $value]
    * @return $this
    **/
  public function setParam( string $key, $value = null ) {
    $this->queryParam[$key] = $value;
    return $this;
  }

  /**
    * withHeader($header)
    * @param  string $header
    * @return $this
    * append $header to CURLOPT_HTTPHEADER
    *
    * $header **must** be a properly formated  HTTP header
    * @example
    *   $curl->to("https://foo.org")
    *   ->withHeader("Accept-Encoding: gzip, deflate, br")
    *   ->get()
    **/
  public function withHeader( string $header ) {
    $this->options[CURLOPT_HTTPHEADER][] = $header;
    return $this;
  }

  /**
    * basicAuth($user,$pass)
    * set header request for basic HTTP auth
    * @param
    *   string $user
    *   string $pass
    * @return $this
    **/
  public function basicAuth( string $user, string $pass) {
    return $this->withHeader(
      "Authorization: Basic " . base64_encode("$user:$pass")
    );
  }

  /**
    * withJson()
    * append the proper "Content-Type" HTTP header to CURLOPT_HTTPHEADER
    * set CURLOPT_POSTFIELDS to a json encoded body
    * @return $this
    **/
  public function withJson($data) {
    $this->options[CURLOPT_HTTPHEADER][] = "Content-Type: application/json";
    $this->options[CURLOPT_POSTFIELDS]   = json_encode($data);
    return $this;
  }

  /**
    * withFile()
    * @return $this
    **/
  public function withFile( string $fullPath, array $info = [] ) {
    $this->options[CURLOPT_POST] = 1;
    $this->options[CURLOPT_POSTFIELDS] = $info;
    $this->options[CURLOPT_POSTFIELDS]['file_contents'] = curl_file_create($fullPath);
    return $this;
  }



  /**
    * get()
    * performs a GET request (default)
    * @return $this
    **/
  public function get() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'GET';
    return $this->query();
  }

  /**
    * post()
    * performs a POST request
    * @return $this
    **/
  public function post() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'POST';
    return $this->query();
  }

  /**
    * put()
    * performs a PUT request
    * @return $this
    **/
  public function put() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'PUT';
    return $this->query();
  }

  /**
    * patch()
    * performs a PATCH request
    * @return $this
    **/
  public function patch() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
    return $this->query();
  }

  /**
    * delete()
    * performs a DELETE request
    * @return $this
    **/
  public function delete() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    return $this->query();
  }

  /**
    * asArray()
    * @return array
    **/
  public function asArray() {
    return json_decode( $this->body, true );
  }

  /**
    * asObject()
    * @return stdClass
    **/
  public function asObject() {
    return json_decode( $this->body, false );
  }


  /**
    * query()
    * performs the request
    * @return $this
    **/
  private function query() {

    $this->formatURL();

    $this->options[CURLOPT_RETURNTRANSFER] = true;
    $this->options[CURLOPT_FOLLOWLOCATION] = true;
    $this->options[CURLOPT_HEADER]         = true;

    $ch = curl_init();
    curl_setopt_array($ch, $this->options);
    $packet = curl_exec($ch);
    $info   = curl_getinfo($ch);
    curl_close($ch);

    $this->statusCode = $info['http_code'];
    $headerSize       = $info['header_size'];
    $this->header     = self::parseLastHeader(substr($packet,0,$headerSize));
    $this->body       = substr($packet,$headerSize);

    return $this;

  }

  /**
    * @param  string  raw HTTP headers
    * @return array   last response $header
    **/
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

  /**
    * @return void
    **/
  private function formatURL() {

    if( empty($this->queryParam) ) { return; }

    $isFirst = true;
    foreach( $this->queryParam as $key => $value ) {

      $key     = urlencode( (string) $key   );
      $value   = urlencode( (string) $value );

      $this->options[CURLOPT_URL] .= $isFirst ? "?" : "&";
      $this->options[CURLOPT_URL] .= $key;
      $isFirst = false;

      $this->options[CURLOPT_URL] .= "=$value";

    }

  }



}
