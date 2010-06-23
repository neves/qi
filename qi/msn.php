<?

/**
 * id de teste: 78dcdfb1e2c91e14
 */
class Qi_MSN
{
	/**
	 * Exibe botão de status no MSN.
	 */
	public static function button($id, $extra = array())
	{
		$default = array(
			"width" => "32",
			"backColor" => "transparent",
			"altBackColor" => "transparent",
			"foreColor" => "transparent"
		);

		$extra = array_merge($default, $extra);
		ob_start();
?>
<script src="http://settings.messenger.live.com/controls/1.0/PresenceButton.js"></script>
<div
  id="Microsoft_Live_Messenger_PresenceButton_<?=$id?>"
 <? foreach($extra as $k => $v): ?>
 msgr:<?=$k?>="<?=$v?>"
 <? endforeach ?> 
  msgr:conversationUrl="http://settings.messenger.live.com/Conversation/IMMe.aspx?invitee=<?=$id?>@apps.messenger.live.com&mkt=pt-br"></div>
<script src="http://messenger.services.live.com/users/<?=$id?>@apps.messenger.live.com/presence?dt=&mkt=pt-br&cb=Microsoft_Live_Messenger_PresenceButton_onPresence"></script>
</div>
<?		return ob_get_clean();
	}

	/**
	 * Exibe iframe de status no MSN.
	 */
	public static function iframe($id, $extra = array())
	{
		$default = array(
			"width" => "300",
			"height" => "300",
			"scrolling" => "no",
			"style" => "border: solid 1px black; overflow: hidden"
		);

		$extra = array_merge($default, $extra);
		$params = "";
		foreach($extra as $k=>$v) $params .= "$k='$v' ";
?>
<iframe src="http://settings.messenger.live.com/Conversation/IMMe.aspx?invitee=<?=$id?>@apps.messenger.live.com&mkt=pt-br" frameborder="0" <?=$params?>></iframe>
<?
	}

	/**
	 * Exibe icone de status no MSN.
	 */
	public static function icon($id, $custom_img = null)
	{
		$msg = "online/offline";
		if ($custom_img):
			$custom_img = <<<T
<img style="border-style: none;" src="$custom_img" />
T;
			$online = self::is_online($id);
			$msg = $online ? "online" : "offline";
			$img = sprintf($custom_img, $online ? "on" : "off");
		else:
			$img = self::img_status($id);
		endif;
		$url = "http://settings.messenger.live.com/Conversation/IMMe.aspx?invitee=$id@apps.messenger.live.com&mkt=pt-br";
		return <<<T
<a target="_blank" title="MSN $msg" alt="MSN $msg" href="$url">$img</a>
T;
	}

	public static function img_status($id)
	{
		$url_status = self::url_status($id);
		return <<<T
<img style="border-style: none;" src="$url_status" width="16" height="16" />
T;
	}

	public static function url_status($id)
	{
		return "http://messenger.services.live.com/users/$id@apps.messenger.live.com/presenceimage?mkt=pt-br";
	}

	public static function is_online($id)
	{
		$s = @fsockopen("messenger.services.live.com", 80);
		$req = "GET /users/$id@apps.messenger.live.com/presenceimage?mkt=pt-br HTTP/1.1\r\n";
		$req .= "Host: messenger.services.live.com\r\n";
		$req .= "Connection: Close\r\n\r\n";
		fwrite($s, $req);
		$return = false;
		while(($line = fgets($s)) !== false):
			if (preg_match("/Offline.gif/", $line) == 1) {$return = false; break;}
			if (preg_match("/Online.gif/", $line) == 1) {$return = true; break;}
		endwhile;
		fclose($s);
		return $return;
	}
}

?>