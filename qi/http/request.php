<?

class Qi_Http_Request
{
  public $method = "GET";
  public $url = "";
  public $headers = array();
  public $body = "";
  public $parsed_response_headers = array();
  public $response_headers = array();
  public $response_body = "";
  public $response_code = 0;

  public function load()
  {
    if ( ! isset($this->headers["Content-Length"]) )
      $this->headers["Content-Length"] = strlen($this->body);
    $context = stream_context_create($this->build_context_options());
    $response = file_get_contents($this->url, false, $context);
    $this->response_headers = $http_response_header;
    $this->_parse_response_headers();
    $this->_parse_response_code();
    return $this->response_body = $response;
  }

  public function get($data = array())
  {
    $this->method = "GET";
    $query_string = http_build_query($data);
    $this->url .= "?$query_string";
    return $this->load();
  }

  public function post($data = array())
  {
    $this->method = "POST";
    $this->headers["Content-type"] = "application/x-www-form-urlencoded";
    $this->body = http_build_query($data);
    return $this->load();
  }

  function build_context_options()
  {
    return array(
      'http' => array(
        'method' => $this->method,
        'header' => $this->build_headers(),
        'content' => $this->body,
        'ignore_errors' => true // continua mesmo se a resposta for um erro
        )
    );
  }

  function build_headers()
  {
    $headers = array();
    foreach($this->headers as $k => $v) $headers[] = "$k: $v";
    return implode("\r\n", $headers);
  }

  protected function _parse_response_headers()
  {
    $this->parsed_response_headers = array();
    foreach($this->response_headers as $header):
      @list($header, $value) = explode(":", $header, 2);
      $this->parsed_response_headers[strtolower($header)] = $value;
    endforeach;
  }
  
  protected function _parse_response_code()
  {
    reset($this->parsed_response_headers);
    $http = key($this->parsed_response_headers);
    preg_match("/\d{3}/", $http, $matches);
    $this->response_code = $matches[0];
  }
}

?>