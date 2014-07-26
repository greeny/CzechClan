<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class LogRepository extends BaseRepository
{
	protected static $browserData = NULL;

	protected static $ip = NULL;

	public function addLog($action, $data)
	{
		$log = new Log();
		$log->time = Time();
		$log->action = $action;
		$log->data = serialize($data);
		$log->browser = serialize(self::getBrowserData());
		$log->ip = self::getIp();
		$this->persist($log);
		return $log;
	}

	public static function getIp()
	{
		return self::$ip ? self::$ip : self::$ip = $_SERVER['REMOTE_ADDR'];
	}

	public static function getBrowserData()
	{
		if(self::$browserData) {
			return self::$browserData;
		}
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$name = $ub = $platform = $version = 'Unknown';

		if(preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		} elseif(preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		} elseif(preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$name = 'Internet Explorer';
			$ub = "MSIE";
		} else if(preg_match('/Firefox/i',$u_agent)) {
			$name = 'Mozilla Firefox';
			$ub = "Firefox";
		} else if(preg_match('/Chrome/i',$u_agent)) {
			$name = 'Google Chrome';
			$ub = "Chrome";
		} else if(preg_match('/Safari/i',$u_agent)) {
			$name = 'Apple Safari';
			$ub = "Safari";
		} else if(preg_match('/Opera/i',$u_agent)) {
			$name = 'Opera';
			$ub = "Opera";
		} else if(preg_match('/Netscape/i',$u_agent)) {
			$name = 'Netscape';
			$ub = "Netscape";
		}

		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . implode('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

		preg_match_all($pattern, $u_agent, $matches);

		$i = count($matches['browser']);
		if($i !== 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			} else {
				$version= $matches['version'][1];
			}
		} else {
			$version= $matches['version'][0];
		}

		if($version === null || $version === "") {
			$version = "?";
		}

		return self::$browserData = array(
			'name' => $name,
			'version' => $version,
			'platform' => $platform,
		);
	}
}
