<?php
/**
 * ownCloud - transmissionwebclient
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Galambos Máté <matega@mensa.hu>
 * @copyright Galambos Máté 2015
 */

namespace OCA\Transmission\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Controller;


class ProxyResponse extends Response {
	private $response="";
	private $content="";
	function __construct($url,$content) {
		$this->content=$content;
		$this->response=$this->load_url($url);
	}
	function render() {
		return $this->response;
	}
	function load_url($url) {
		$proxied_host="localhost";
		$proxied_port=9091;
		$c=curl_init();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			curl_setopt($c, CURLOPT_POST, 1);       
			curl_setopt($c, CURLOPT_POSTFIELDS, $this->content);
		}
		curl_setopt($c, CURLOPT_URL, "$proxied_host:$proxied_port/transmission/".$url);
		curl_setopt($c, CURLOPT_HEADER,1);
		$outheaders=[];

// Infos 
// http://www.popmartian.com/tipsntricks/2015/07/14/howto-use-php-getallheaders-under-fastcgi-php-fpm-nginx-etc/
// http://php.net/manual/en/function.getallheaders.php

		if (!function_exists('getallheaders')) {
			function getallheaders() {
				$headers = [];
				foreach ($_SERVER as $name => $value) {
					if (substr($name, 0, 5) == 'HTTP_') {
						$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
						}
				}
				return $headers;
			}
		}
		$outtmp=getallheaders();
		foreach ($outtmp as $k => $v) {
			$outheaders[]="$k: $v";	
		}
		curl_setopt($c, CURLOPT_HTTPHEADER, $outheaders);
		curl_setopt($c, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($c, CURLOPT_TIMEOUT,30);
		$res=curl_exec($c);
		$headers=[];
		$tmp="";
		$arr=explode("\r\n\r\n",$res,2);
		$brr=explode("\n",$arr[0]);
		$hd=$brr[0];
		$this->setStatus(explode(" ",$hd)[1]);
		unset($brr[0]);
		foreach($brr as $v) {
			$h=explode(": ",$v);
			$headers[$h[0]]=$h[1];
		}
		$this->setHeaders($headers);
		return $arr[1];
	}
}

class ProxyController extends Controller {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}
	/**
	 * Proxy method for GET requests
	 * @NoAdminRequired
         * @NoCSRFRequired
	 */
	public function get($path) {
		return new ProxyResponse($path,"");
	}
	/**
	 * Proxy method for POST requests
	 * @NoAdminRequired
         * @NoCSRFRequired
	 */
	public function post($path){
		return new ProxyResponse($path,file_get_contents("php://input"));
	}


}
