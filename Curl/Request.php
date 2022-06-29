<?php

namespace NoLibForIt\Curl;

class Request {

  protected $options    = [];
  protected $queryParam = []; /* [ ["foo" => null ], ["foo" => 1] ] */
  public    $answer;

  /**
    * instanciate with location
    * @static
    * @param   string   $url
    * @return  Request  new self
    */
  public static function to( string $url ) {
    return (new self)->location($url);
  }
  /**
    * set location (CURLOPT_URL)
    * @param  string  $url
    * @return Request $this
    */
  public function location( string $url ) {
    $this->options[CURLOPT_URL] = $url;
    return $this;
  }
  /**
    * add query params
    * @param  array   $params
    * @return Request $this
    * @example
    *   withParams([ ["a"=>1], ["a"=>2], ["b"] ]) requests with "?a=1&a=2&b"
    *   withParams([ ["a"=>1], ["a"=>2 ,  "b"] ]) requests with "?a=1&a=2&b"
    */
  public function withParams( array $params ) {
    foreach( $params as $key => $value ) {
      $this->addParam( $key, $value );
    }
    return $this;
  }
  /**
    * add single query param
    * @param  string $key
    *         mixed  $value
    * @return $this
    */
  public function addParam( string $key, $value = null ) {
    $this->queryParam[] = [ $key => $value ];
    return $this;
  }
  /**
    * append $header to CURLOPT_HTTPHEADER
    * @param  string $header
    * @return $this
    *
    * $header **must** be a properly formated  HTTP header
    * @example
    *   $curl->to("https://foo.org")
    *   ->withHeader("Accept-Encoding: gzip, deflate, br")
    *   ->get()
    */
  public function withHeader( string $header ) {
    $this->options[CURLOPT_HTTPHEADER][] = $header;
    return $this;
  }

  /**
    * basic HTTP auth
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
    * set CURLOPT_POSTFIELDS to a json encoded body
    * append the proper "Content-Type" HTTP header to CURLOPT_HTTPHEADER
    * @param  mixed   object
    * @return $this
    */
  public function withJson($object) {
    $this->withHeader("Content-Type: application/json");
    $this->options[CURLOPT_POSTFIELDS] = json_encode($object);
    return $this;
  }

  /**
    * append a file
    * @param  string  path to file
    * @param  array   file information
    * @return $this
    **/
  public function withFile( string $fullPath, array $info = [] ) {
    $this->options[CURLOPT_POST] = 1;
    $this->options[CURLOPT_POSTFIELDS] = $info;
    $this->options[CURLOPT_POSTFIELDS]['file_contents'] = curl_file_create($fullPath);
    return $this;
  }

  /**
    * performs a GET request
    * @return $this
    **/
  public function get() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'GET';
    return $this->query();
  }

  /**
    * performs a POST request
    * @return $this
    **/
  public function post() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'POST';
    return $this->query();
  }

  /**
    * performs a PUT request
    * @return $this
    **/
  public function put() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'PUT';
    return $this->query();
  }

  /**
    * performs a PATCH request
    * @return $this
    **/
  public function patch() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
    return $this->query();
  }

  /**
    * performs a DELETE request
    * @return $this
    **/
  public function delete() {
    $this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    return $this->query();
  }

  /**
    * dump representation of this
    * @return array
    */
  public function dump() {
    return [
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
    ];
  }

  /**
    * dump and die
    * @return void
    */
  public function dd() {
    header("Content-Type: application/json");
    echo json_encode( $this->dump() );
    die();
  }



  /**
    * performs the request
    * @return Answer
    **/
  private function query() {

    $this->formatURL();

    $this->options[CURLOPT_RETURNTRANSFER] = true;
    $this->options[CURLOPT_FOLLOWLOCATION] = true;
    $this->options[CURLOPT_HEADER]         = true;

    $this->answer = new Answer($this->options);

    return $this->answer;

  }

  /**
    * format URL
    * @return void
    */
  private function formatURL() {

    if( empty($this->queryParam) ) {
      return;
    }

    $isFirst = true;
    foreach( $this->queryParam as $param ) {
      foreach( $param as $key => $value ) {

        $key     = urlencode( (string) $key   );
        $value   = urlencode( (string) $value );

        $this->options[CURLOPT_URL] .= $isFirst ? "?" : "&";
        $this->options[CURLOPT_URL] .= $key;
        $isFirst = false;

        $this->options[CURLOPT_URL] .= "=$value";

      }
    }

  }



}
