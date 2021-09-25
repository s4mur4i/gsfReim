<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Curllib
{
    public function makeRequest($type, $url, $postdata = '', $headers = array(), $withResp = FALSE){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'GSF Affordable Care');

		if($type == "POST"){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}

		if(count($headers) > 0){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$res = curl_exec($ch);
        $d = preg_split("#\n\s*\n#Uis", $res);
        $body = $d[1];
        $h = $d[0];
        $headers = array();
        foreach(explode("\n",$h) as $header) {
                $data = explode(":",$header);
                $key = $data[0];
                unset($data[0]);
                $value = trim(implode(":",$data));
                $headers[$key] = $value;
        }
		if($withResp){
			$ci = curl_getinfo($ch);
			curl_close($ch);
			return array_merge($ci, array("body" => $body), $headers);
		} else {
			curl_close($ch);

			return $body;
		}
	}
}
