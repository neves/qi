<?

require_once __DIR__."/../http/request.php";

class Qi_Gdata_ClientLogin
{
  const ANALYTICS = "analytics";
  const APPS = "apps";	
  const BASE = "gbase";
  const SITES = "jotspot";
  const BLOGGER = "blogger";
  const BOOK = "print";
  const CALENDAR = "cl";
  const CODE = "codesearch";
  const CONTACTS = "cp";
  const DOCS = "writely";
  const FINANCE = "finance";
  const GMAIL = "mail";
  const HEALTH = "health";
  const MAPS = "local";
  const PICASA = "lh2";
  const SIDEWIKI = "annotateweb";
  const SPREADSHEETS = "wise";
  const WEBMASTER = "sitemaps";
  const YOUTUBE = "youtube";
  const FUSION_TABLES = "fusiontables";
  const URL = "https://www.google.com/accounts/ClientLogin";

  public $account_type = "HOSTED_OR_GOOGLE"; // HOSTED, GOOGLE
  public $email = "";
  public $passwd = "";
  public $service = "";
  public $company = "qi";
	public $app = "gdata";
	public $version = "1.0";
  public $auth = "";

  public function auth()
  {
    $request = new Qi_Http_Request();
    $request->url = self::URL;
    $response = trim($request->post($this->_build_post()));
    if ($response == "Error=BadAuthentication")
      throw new RuntimeException($response);
    preg_match("/Auth=(\S+)/", $response, $matches);
    return $this->auth = $matches[1];
  }

  public static function static_auth($email, $passwd = null, $service = null, $account_type = "HOSTED_OR_GOOGLE",
                                     $company = "qi", $app = "gdata", $version = "1.0")
  {
    if (is_array($email)) extract($email);
    $login = new Qi_Gdata_ClientLogin;
    $login->email = $email;
    $login->passwd = $passwd;
    $login->service = $service;
    $login->company = $company;
    $login->app = $app;
    $login->version = $version;
    $login->account_type = $account_type;
    return $login->auth();
  }

  protected function _build_post()
  {
    return array(
      "accountType" => $this->account_type,
      "Email" => $this->email,
      "Passwd" => $this->passwd,
      "service" => $this->service,
      "source" => "{$this->company}-{$this->app}-{$this->version}"
    );
  }
}

?>