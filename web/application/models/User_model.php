<?php
class User_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
		$this->crowdUrl = $this->config->item("CROWD_URL");;
		$this->crowdUser = $this->config->item("CROWD_USERNAME");
		$this->crowdPass = $this->config->item("CROWD_PASSWORD");
		$this->managerToken = $this->config->item("MANAGER_TOKEN");
		$this->managerBase = $this->config->item("MANAGER_BASE");
		$this->managerCreds = base64_encode(sprintf("%s:%s", $this->config->item("MANAGER_CLIENT_ID"), $this->config->item("MANAGER_CLIENT_SECRET")));
		$this->authGroups = $this->config->item("AUTH_GROUPS");
	}
	function get_val($str, $key1, $key2) {
		if(strpos('@@@' . $str, $key1) <> 0 && strpos('@@@' . $str, $key2) <> 0) {
			$MyVal = explode($key1, $str);
			$MyVal2 = explode($key2, $MyVal[1]);
			return $MyVal2[0];
		} else {
			return '';
		}
	}

	function check_login($user,$password){
		$auth_method = $this->config->item("AUTH_METHOD");
		$getStats = $this->config->item("getStats");
		$return = array();
		$return['err'] = FALSE;
		$isReim = 0;
		$isReimDir = 0;
		$inCapSwarm = 0;
		$isCapDir = 0;

		if($auth_method == "CROWD"){
			$headers = array("Authorization: Basic " . base64_encode($this->crowdUser.":".$this->crowdPass), 'Content-Type: application/json', 'Accept: application/json');
			$requestBody = json_encode(array("value" => $password));
			$req = $this->curllib->makeRequest('POST', $this->crowdUrl."authentication?username=" . urlencode($user), $requestBody, $headers, TRUE);

			$crowdData = json_decode($req['body'], TRUE);

			if($req['http_code'] != 200){
				if($req['http_code'] == 400){				
					if(isset($crowdData['reason']) && $crowdData['reason'] == "USER_NOT_FOUND"){
						$return['err'] = "BAD USER";
						$return['errReason'] = $crowdData['reason'];
						$return['errMessage'] = $crowdData['message'];
					} else {
						$return['err'] = "LOGINERR";
						$return['errReason'] = sprintf("Bad response from Crowd during authentication: %d", $req['http_code']);
						$return['errMessage'] = sprintf("Bad response from Crowd during authentication: %d", $req['http_code']);
					}
				} else {
					$return['err'] = "LOGINERR";
					$return['errReason'] = sprintf("Bad response from Crowd during authentication: %d", $req['http_code']);
					$return['errMessage'] = sprintf("Bad response from Crowd during authentication: %d", $req['http_code']);
				}
				return $return;
			}

			$groupReq = $this->getGroups($user);
			$groupData = $groupReq['GROUPS'];
			$return['groupData'] = $groupData;
			if($groupReq['HTTP_CODE'] != 200){
				$return['err'] = "LOGINERR";
				$return['errReason'] = sprintf("Bad response from Crowd while checking groups: %d", $groupReq['HTTP_CODE']);
				$return['errMessage'] = sprintf("Bad response from Crowd while checking groups: %d", $groupReq['HTTP_CODE']);
				return $return;
			}
			
			if(count($groupData) <= 0){
				$d = json_encode($groupData);
				log_message("error", "Something went wrong getting groups for $user. The data returned was: $d");

				$return['err'] = "LOGINERR";
				$return['errReason'] = 'Something happened on login getting groups';
				$return['errMessage'] = 'Something happened on login getting groups';
			} else {
				//First thing we need to do before moving forward is check to see if this user is in a banned group.
				$groupBans = $this->db->select("groupName, banDate, bannedBy, reason")->where_in("groupName", $groupData)->where("active", '1')->get("bannedGroups");

				if($groupBans->num_rows() > 0){
					$gName = $groupBans->row(0)->groupName;
					$bannedBy = $groupBans->row(0)->bannedBy;
					$banDate = $groupBans->row(0)->banDate;

					$return['err'] = "GROUPBAN";
					$return['errReason'] = $groupBans->row(0)->reason;
					$return['errMessage'] = "Group ban issued to $gName by $bannedBy on $banDate.";

				}
				if(is_array($this->authGroups['REIMBURSEMENT'])){
					foreach($this->authGroups['REIMBURSEMENT'] as $g) {
						if(in_array($g, $groupData)){
							$isReim = 1;
						}
					}
				} else {
					if(in_array($this->authGroups['REIMBURSEMENT'], $groupData)){
						$isReim = 1;
					}
				}

				if(is_array($this->authGroups['REIMDIRS'])){
					foreach($this->authGroups['REIMDIRS'] as $g){
						if(in_array($g, $groupData)){
							$isReimDir = 1;
						}
					}
				} else {
					if(in_array($this->authGroups['REIMDIRS'], $groupData)){
						$isReimDir = 1;
					}
				}

				if(is_array($this->authGroups['CAPSWARM'])){
					foreach($this->authGroups['CAPSWARM'] as $g){
						if(in_array($g, $groupData)){
							$inCapSwarm = 1;
						}
					}
				} else {
					if(in_array($this->authGroups['CAPSWARM'], $groupData)){
						$inCapSwarm = 1;
					}
				}
				
				if(is_array($this->authGroups['CAPSWARM_REIM'])){
					foreach($this->authGroups['CAPSWARM_REIM'] as $g){
						if(in_array($g, $groupData)){
							$isCapDir = 1;
							$isReimDir = 1;
						}
					}
				} else {
					if(in_array($this->authGroups['CAPSWARM_REIM'],$groupData)){
						$isCapDir = 1;
						$isReimDir = 1;
					}
				}
				
				foreach($this->authGroups['ADMINS'] as $admins){
					if(in_array($admins, $groupData)){
						$isReim = 1;
						$isReimDir = 1;
						$isCapDir = 1;
						$inCapSwarm = 1;
					}
				}
				
				$return['isReim'] = $isReim;
				$return['isReimDir'] = $isReimDir;
				$return['inCapSwarm'] = $inCapSwarm;
				$return['isCapDir'] = $isCapDir;

			}
		
		} elseif($auth_method == "INTERNAL"){
			$groups = array();
			$uchk = $this->db->select('user, id, gids')->where('user', $user)->where('password', sha1($password))->where('active', 1)->get('users');
			if($uchk->num_rows() > 0){
				$uid = $uchk->row(0)->id;
				if(strlen($uchk->row(0)->gids) > 0){
					$gids = explode(",",$uchk->row(0)->gids);
					if(in_array(1, $gids)){
						$isReim = 1;
					}
					if(in_array(2, $gids)){
						$isReimDir = 1;
					}
					if(in_array(3, $gids)){
						$inCapSwarm = 1;
					}
					if(in_array(4, $gids)){
						$isCapDir = 1;
						$isReimDir = 1;
					}
				}
				$return['isReim'] = $isReim;
				$return['isReimDir'] = $isReimDir;
				$return['inCapSwarm'] = $inCapSwarm;
				$return['isCapDir'] = $isCapDir;
			} else {
				$return['err'] = "BAD USER";
				$return['errReason'] = "Invalid username/password.";
				$return['errMessage'] = "Invalid username/password.";
			}
		} else {
			$return['err'] = "BAD AUTH METHOD";
			$return['errReason'] = "NO AUTH METHOD SET";
			$return['errMessage'] = "NO AUTH METHOD SET";
		}

		return $return;
	}

	function getGroups($user){
		$headers = array("Authorization: Basic " . base64_encode($this->crowdUser.":".$this->crowdPass), 'Content-Type: application/xml', 'Accept: application/xml');
		$req = $this->curllib->makeRequest('GET', $this->crowdUrl."user/group/direct?username=".urlencode($user),'',$headers, TRUE);

		$groupData = $req['body'];

		$ret = array("HTTP_CODE" => $req['http_code'], "GROUPS" => array());

		if($req['http_code'] != 200) {
			return $ret;
		}

		$gXMLData = simplexml_load_string($groupData);
		$groups = array();
		foreach($gXMLData->group as $group){
			$groups[] = (string) $group->attributes()->name[0];
		}

		$ret['GROUPS'] = $groups;
		return $ret;
	}

	function getMemberID($user){
		$vrdb = $this->load->database("vilerat",TRUE);
		$sql = "SELECT member_id FROM gsfForums.members WHERE replace(name, '&#39;', '\'') = ?";
		$member_data = $vrdb->query($sql, array($user));
		if($member_data->num_rows() > 0){
			return $member_data->row(0)->member_id;
		} else {
			return 0;
		}
	}

	function getLoginStats($name){
		$memberID = $this->getMemberID($name);
		if($memberID == 0){
			log_message("error", "Unable to get member ID for $name.");
			return array();
		}
		$headers = array(sprintf("Authorization: Basic %s", $this->managerCreds));
		$data = $this->curllib->makeRequest("GET", sprintf("%s/Account/%s/Activity", $this->managerBase, $memberID),"", $headers);
		$decoded = json_decode($data, TRUE);

		if(count($decoded)){
			return $decoded;
		} else {
			return array();
		}
	}

	function registerUser($user, $password) {
		$auth_method = $this->config->item("AUTH_METHOD");
		if($auth_method == "INTERNAL"){
			$uchk = $this->db->where('user', $user)->get('users');
			if($uchk->num_rows() > 0){
				$return['err'] = "User Exists. Please refresh the page and try again, or wait 3 seconds for the page to automatically refresh.";
			} else {
				$this->db->insert("users", array('user' => $user, "password" => sha1($password)));
				$return['err'] = FALSE;
				$return['message'] = "User Created.";
			}
			return $return;
		} else {
			show_error("Invalid request. Auth method is not set to internal, cannot register user.", 500);
		}
	}
}
