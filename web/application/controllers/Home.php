<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	function __construct(){
		parent::__construct();
		$this->logged_in = FALSE;
		$this->isReim = FALSE;
		$this->isReimDir = FALSE;
		$this->isBanned = FALSE;
		$this->isCapDir = FALSE;
		$this->vars = $this->session->userdata('vars');
		$this->accLosses = FALSE;
	 	if($this->vars['logged_in'] == TRUE){
	 		$this->logged_in = TRUE;
			if($this->vars['isReim'] == 1){
		 		$this->isReim = TRUE;
		 	}
			if($this->vars['isReimDir'] == 1){
		 		$this->isReimDir = TRUE;
		 	}
			if($this->vars['isBanned'] == 1){
				$this->isBanned = TRUE;
			}
			if($this->vars['isCapDir'] == 1){
				$this->isCapDir = TRUE;
			}
			$acceptLosses = $this->db->where('name','acceptLosses')->get('adminSettings');
			$this->accLosses = $acceptLosses->row(0)->value;
	 	}
	}

	function createAdmin(){
		if($this->config->item('AUTH_METHOD') == "INTERNAL"){
			$dbchk = $this->db->where('user', 'admin')->get('users');
			if($dbchk->num_rows() == 0){
				$dti = array("user" => "admin", "password" => sha1($this->config->item('ADMIN_PASSWORD')), 'gids' => '1,2,3', 'id' => 1);
				$this->db->insert('users', $dti);
				echo "Admin user created.";
			} else {
				echo "The admin account already exists.";
			}
		} else {
			echo "This site is not configured to use internal auth.";
		}
	}

	public function index() {
		if($this->logged_in){
			$data['kills'] = $this->db->select("k.killID, k.bcast, k.submittedBy, k.corpName, a.allianceName, k.victimName, k.killTime, k.sysName, k.regName, k.shipName, k.attackers, k.fit, k.overPtCap, k.crest_link")
					->from("kills k")
					->join("corporations c", "c.corpID = k.corpID", "left")
					->join("alliances a", "a.allianceID = c.allianceID", "left")
					->where("k.paid", 0)
					->where("k.reservedBy", $this->session->userdata('vars')['user'])
					->order_by("k.timestamp", "ASC")
					->get();
			$data['allKills'] = $this->db->select("k.killID, k.bcast, k.submittedBy, k.corpName, a.allianceName, k.victimName, k.killTime, k.sysName, k.regName, k.shipName, k.attackers, k.fit, k.overPtCap")
					->from("kills k")
					->join("corporations c", "c.corpID = k.corpID", "left")
					->join("alliances a", "a.allianceID = c.allianceID", "left")
					->where("k.paid", 0)
					->where("k.reservedBy", NULL)
					->get();
		}
		if($this->vars['err']){
			$data['err'] = TRUE;
			$data['errMessage'] = $this->vars['errMessage'];
		} else {
			$data['err'] = FALSE;
		}
		$acceptLosses = $this->db->where('name','acceptLosses')->get('adminSettings');
		$data['acceptLosses'] = $acceptLosses->row(0)->value;

		$this->load->view('header');
		$this->load->view('home', $data);
		$this->load->view('footer');
	}

	function submitKill(){
		$user = $this->vars['user'];
		$carriers = array(19720,19722,19724,19726,34339,34341,34343,34345,23757,23911,23915,24483,37604,37605,37606,37607);

		if($this->accLosses == 1){
			if($this->logged_in && !$this->isBanned){
				$crestLink = $this->input->post('crestLink', TRUE);
				$bcastText = $this->input->post('bcast', TRUE);
				$cD = $this->getKillData($crestLink);
				if($cD['err']){
					echo "An error occurred: ".$cD['errMsg'];
					return;
				}

				$crestData = $cD['data'];
				//Log the shit 
				$dti = array(
				             "user"		=> $this->session->userdata('vars')['user'],
				             "type"		=> "kill_submit",
				             "data"		=> json_encode(array("IP" => $this->input->ip_address(), "KillID" => $crestData['killID'], "CrestLink" => $crestLink))
				             );
				$this->db->insert("ulog", $dti);

				$curDate = new DateTime("now");
				$killTime = str_replace('.','-',$crestData['killTime']);
				$killDate = new DateTime($killTime);
				$diff = $curDate->diff($killDate);
				$dayDiff = $diff->format('%a');
				$aDd = $this->db->where('name', 'maxDayDiff')->get('adminSettings');
				$allowedDateDiff = $aDd->row(0)->value;

				if($dayDiff <= $allowedDateDiff){
					$sysChk = $this->db->where('sys_eve_id', $crestData['sysID'])->get('vwsysconreg');
					$regID = $sysChk->row(0)->reg_id;
					$regName = $sysChk->row(0)->reg_name;
					$secStatus = $sysChk->row(0)->sec;
					$ptQualified = 0;
					$overPtCap = 0;

					$ptChk = $this->db->where('regID', $regID)->get("ptRegions");
					if($ptChk->num_rows() > 0){
						$ptQualified = 1;
						$ptCap = $this->db->where('name', 'ptCap')->get('adminSettings');
						$ptCapVal = $ptCap->row(0)->value;
						$d = $killDate->format('Y-m');
						$ptAmtChk = $this->db->where('submittedBy', $user)->where('month', $d)->get('vwptcap');
						if($ptAmtChk->num_rows() > 0 && $ptAmtChk->row(0)->total >= $ptCapVal){
							$overPtCap = 1;
						}
					}
					
					//Check to see if the kill exists
					$dbchk = $this->db->where('killID', $crestData['killID'])->get('kills');
					if($dbchk->num_rows() > 0){
						//It exists, so notify user as such
						echo "This kill has already been submitted.";
					} elseif ((in_array($crestData['vicShipTypeID'],$carriers) && $this->vars['inCapSwarm'] == 1) || !in_array($crestData['vicShipTypeID'],$carriers)) {
						//Kill does NOT exist in db, so lets see if there is a payout set for this ship
						$poChk = $this->db->select('p.payoutAmount, p.payoutType, p.typeID, p.id, p.typeName')->from('payouts p')->join('payoutTypes pt','pt.id = p.payoutType')->where('p.typeID', $crestData['vicShipTypeID'])->where('pt.active', '1')->get();
						$payoutArr = array();
						if($poChk->num_rows() > 0){
							if($crestData['vicCorpID'] == 667531913){
								$bonusamt = $this->db->where('name', 'waffeBonus')->get('adminSettings');
								$bonusCap = $this->db->where('name', 'waffeBonusCap')->get('adminSettings');
								if($bonusamt->num_rows() > 0){
									$bonus = $bonusamt->row(0)->value;
								} else {
									$bonus = 0;
								}
								if($bonusCap->num_rows() > 0){
									$bonusCapAmt = $bonusCap->row(0)->value;
								} else {
									$bonusCapAmt = 0;
								}
							}
							foreach($poChk->result() as $row){
								if($crestData['vicCorpID'] == 667531913){
									$bonusPay = $row->payoutAmount * $bonus;
									if($bonusPay <= $bonusCapAmt){
										$payout = $row->payoutAmount + $bonusPay;
									} else {
										$payout = $row->payoutAmount + $bonusCapAmt;
									}
								} else {
									$payout = $row->payoutAmount;
								}
								$payoutArr[$row->payoutType] = $payout;
							}
							$payouts = serialize($payoutArr);
							$dti = array('killID' => $crestData['killID'],
										'killTime' => $crestData['killTime'],
										'crest_link' => $crestLink,
										'bcast' => $bcastText,
										'fit' => serialize($crestData['fit']),
										'attackers' => serialize($crestData['attackerArr']),
										'victimName' => $crestData['vicName'],
										'victimID' => $crestData['vicID'],
										'corpID' => $crestData['vicCorpID'],
										'corpName' => $crestData['vicCorpName'],
										'shipID' => $crestData['vicShipTypeID'],
										'shipName' => $crestData['vicShipTypeName'],
										'sysID' => $crestData['sysID'],
										'sysName' => $crestData['sysName'],
										'regID' => $regID,
										'regName' => $regName,
										'secStatus' => $secStatus,
										'availablePayouts' => $payouts,
										'submittedBy' => $user,
										'ptQualified' => $ptQualified,
										'overPtCap' => $overPtCap);
							$this->db->insert('kills', $dti);

							//Add a thing to process the corporation info and insert that shit too.
							$this->updateCorpData($crestData['vicCorpID']);
							echo sprintf("The loss for %s in a %s has been submitted, thank you.",$crestData['vicName'],$crestData['vicShipTypeName']);
						} else {
							echo "We are currently not reimbursing this ship type. If you feel this is in error, please contact the reimbursement team.";
						}
					} else {
						echo "You are not currently in capswarm and thus not eligible for peace time reimbursement for a carrier.";
					}
				} else {
					echo "This loss is too old. You may only submit losses up to <b>" . $allowedDateDiff . "</b> days. Your loss is <b>" . $dayDiff."</b> days old.";
				}
			} else {
				echo "You are either not logged in, or you have been banned from reimbursement.";
			}
		} else {
			echo "We are not currently accepting losses, please check again later.";
		}
	}

	function myReserved(){
		if($this->isReim || $this->isReimDir){
			$user = $this->vars['user'];
			$data['reserved'] = $this->db->where('reservedBy', $user)->where('paid', '0')->get("kills");

			$this->load->view('header');
			$this->load->view('myReserved', $data);
			$this->load->view('footer');
		}
	}
	function claimBlock(){
		if($this->isReim || $this->isReimDir){
			$user = $this->vars['user'];
			$numRes = $this->input->post('numRes', TRUE);

			$capGroups = array(1538,547,485,659,30);
			$cData = $this->db->where_in("groupID", $capGroups)->get("invTypes");
			$caps = array();
			foreach ($cData->result() as $row){
				$caps[] = $row->typeID;
			}

			if($this->isCapDir){
				$capsOnly = $this->input->post("capsOnly", TRUE);
				if($capsOnly == 1){
					$rowsToRes = $this->db->where("paid", 0)->where("reservedBy", NULL)->where_in("shipID", $caps)->order_by("timestamp", "ASC")->limit($numRes)->get("kills");
				} else {
					$rowsToRes = $this->db->where('paid', 0)->where('reservedBy', NULL)->order_by("timestamp",'ASC')->limit($numRes)->get('kills');
				}
			} else {
				$rowsToRes = $this->db->where('paid', 0)->where('reservedBy', NULL)->where_not_in("shipID", $caps)->order_by("timestamp",'ASC')->limit($numRes)->get('kills');
			}

			if($rowsToRes->num_rows() > 0){
				foreach($rowsToRes->result() as $row){
					$dtu = array('reservedBy' => $user, 'reservedDate' => date('Y-m-d H:i:s'));
					$this->db->where('killID', $row->killID)->update('kills',$dtu);
				}
				echo "Your block has been reserved.";
			} else {
				echo "There are no losses to reserve.";
			}
		}
	}
	function releaseKill(){
		if($this->isReim || $this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			$user = $this->vars['user'];

			$dbChk = $this->db->where('killID', $killID)->get('kills');
			if($dbChk->num_rows() > 0){
				if($user == $dbChk->row(0)->reservedBy){
					if($dbChk->row(0)->paid == 0){
						$dtu = array('reservedBy' => NULL);
						$this->db->where('killID', $killID)->update('kills', $dtu);
						echo "The kill has been released.";
					} else {
						echo "You cannot release a loss that has already been paid or denied.";
					}
				} else {
					echo "You are not allowed to release someone elses reserved loss. Fuck off.";
				}
			} else {
				echo "I could not find the kill specified, please try again. If the problem persists, please contact an admininstrator. " . $killID;
			}
		}
	}

	function mySubmitted(){
		if($this->logged_in){
			$data['submitted'] = $this->db->where('submittedBy',$this->vars['user'])->order_by('timestamp', 'DESC')->get('vwkills');

			$this->load->view('header');
			$this->load->view('mySubmitted', $data);
			$this->load->view('footer');
		}
	}

	function viewPayouts(){
		$payouts = $this->db->order_by('shipName', 'ASC')->get('vwpayoutTypeByShip');
		$data['payoutTypes'] = $this->db->select("DISTINCT(typeName) AS typeName")->get('vwpayoutTypeByShip');

		$payArr = array();
		foreach($payouts->result() as $row){
			$payArr[$row->shipName][$row->typeName] = array("payout" => $row->amount, "insProfit" => $row->insProfit, "totalReim" => $row->totalReim);
		}
		$data['payouts'] = $payArr;

		$this->load->view('header');
		$this->load->view('viewPayouts', $data);
		$this->load->view('footer');
	}
	function updateAvailablePayouts(){
		if($this->isReim || $this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			$killData = $this->db->where('killID', $killID)->get('kills');
			if($killData->num_rows() > 0){
				$vicCorpID = $killData->row(0)->corpID;
				$vicShipTypeID = $killData->row(0)->shipID;
				$poChk = $this->db->where('typeID', $vicShipTypeID)->get('payouts');
				$payoutArr = array();
				if($poChk->num_rows() > 0){
					if($vicCorpID == 667531913){
						$bonusamt = $this->db->where('name', 'waffeBonus')->get('adminSettings');
						$bonusCap = $this->db->where('name', 'waffeBonusCap')->get('adminSettings');
						if($bonusamt->num_rows() > 0){
							$bonus = $bonusamt->row(0)->value;
						} else {
							$bonus = 0;
						}
						if($bonusCap->num_rows() > 0){
							$bonusCapAmt = $bonusCap->row(0)->value;
						} else {
							$bonusCapAmt = 0;
						}
					}
					foreach($poChk->result() as $row){
						if($vicCorpID == 667531913){
							$bonusPay = $row->payoutAmount * $bonus;
							if($bonusPay <= $bonusCapAmt){
								$payout = $row->payoutAmount + $bonusPay;
							} else {
								$payout = $row->payoutAmount + $bonusCapAmt;
							}
						} else {
							$payout = $row->payoutAmount;
						}
						$payoutArr[$row->payoutType] = $payout;
					}
					$payouts = serialize($payoutArr);
					$dtu = array('availablePayouts' => $payouts);
					$this->db->where('killID', $killID)->update('kills', $dtu);

					echo "The payouts available have been updated.";

				} else {
					echo "There are no payout options available for this ship. No changes made.";
				}
			} else {
				echo "Something went wrong, could not find kill " . $killID;
			}
		}
	}

	function updateCorpData($corpID){
		//Get the public corp info from ESI, store it into `corporations` and then grab the public alliance data and store that as well.
		$dataArray = $this->getCorporation($corpID);
		if($dataArray){
			if(isset($dataArray['alliance_id'])){
				$allianceID = $dataArray['alliance_id'];
			} else {
				$allianceID = NULL;
			}
			$dti = array(
			             "corpID"	=> $corpID,
			             "corpName"	=>	$dataArray['name'],
			             "allianceID"	=>	$allianceID,
			             "ticker"	=>	$dataArray['ticker']
			             );
			$this->db->replace("corporations", $dti);
		}

		if($allianceID > 0){
			$dataArray = $this->getAlliance($allianceID);
			if($dataArray){
				$dti = array(
				             "allianceID"	=> $allianceID,
				             "allianceName"	=>	$dataArray['name'],
				             "ticker"	=>	$dataArray['ticker']
				             );
				$this->db->replace("alliances", $dti);
			}
		}
	}

	function getKillData($link) {
		$ret = array("err" => FALSE, "errMsg" => "", "data" => array());
		$low = array('11','12','13','14','15','16','17','18');
		$med = array('19','20','21','22','23','24','25','26');
		$high = array('27','28','29','30','31','32','33','34');
		$rigs = array('92','93','94','95','96','97','98','99');
		$fit = array();
		// CCP changed the links in game to ESI, which uses a different case for var names, so now we need to handle that. We want to accept both CREST and ESI, so this is the solution.
		if(strpos($link, "crest") > 0) {
			//CREST Link
			$cD = $this->curllib->makeRequest('GET', $link);
			$crestData = json_decode($cD, TRUE);
			if(isset($crestData['exceptionType']) && $crestData['exceptionType'] == 'ForbiddenError'){
				$ret['errMsg'] = "There was an error, please check your link and try again. If you continue to see this error, please contact an administrator.";
				$ret['err'] = TRUE;
				return $ret;
			}
			$ret['data']['killID'] = $crestData['killID'];
			$ret['data']['killTime'] = $crestData['killTime'];
			$ret['data']['numAttackers'] = $crestData['attackerCount'];
			$ret['data']['sysID'] = $crestData['solarSystem']['id'];
			$ret['data']['sysName'] = $crestData['solarSystem']['name'];
			$ret['data']['vicName'] = $crestData['victim']['character']['name'];
			$ret['data']['vicID'] = $crestData['victim']['character']['id'];
			$ret['data']['vicShipTypeID'] = $crestData['victim']['shipType']['id'];
			$ret['data']['vicShipTypeName'] = $crestData['victim']['shipType']['name'];
			$ret['data']['vicCorpID'] = $crestData['victim']['corporation']['id'];
			$ret['data']['vicCorpName'] = $crestData['victim']['corporation']['name'];
			$ret['data']['damageTaken'] = $crestData['victim']['damageTaken'];
			$attackers = $crestData["attackers"];
			$attackerArr = array();
			$i = 0;
			if(count($attackers) > 0){
				foreach($attackers as $key => $value){
					$alliance = '';
					if(isset($value['alliance'])){
						$alliance = $value['alliance']['name'];
					}
					if(isset($value['shipType'])){
						$shiptype = $value['shipType']['name'];
					} else {
						$shiptype = "Unknown";
					}
					if(isset($value['character'])){
							$character = $value['character']['name'];
					} else {
						$character = 'Unknown ' . $i;
					}
					if(isset($value['weaponType'])){
						$weaponType = $value['weaponType']['name'];
					} else {
						$weaponType = "Unknown";
					}
					if(isset($value['corporation'])){
						$corporation = $value['corporation']['name'];
					} else {
						$corporation = "Unknown Corporation";
					}
					$attackerArr[$character] = array('corporation' => $corporation,
													'alliance' => $alliance,
													'shipType' => $shiptype,
													'weaponType' => $weaponType,
													'damageDone' => $value['damageDone_str']);
					$i++;
				}
				$ret['data']['attackerArr'] = $attackerArr;
			} else {
				$errid = uniqid();
				$ret['err'] = TRUE;
				$ret['errMsg'] = "Something went wrong and it was probably CCP's fault. Try again later. If it still doesn't work, give this to kilgarth: " . count($attackers) . " | ".$errid;
				$fn = $errid.".txt";
				file_put_contents($fn, sprintf("%s: %s",$link,$cD));
				return $ret;
			}

			foreach($crestData['victim']['items'] as $item){
				if(in_array($item['flag'], $high)){
					$fit['high'][] = $item['itemType']['name'];
				} elseif(in_array($item['flag'], $med)){
					$fit['med'][] = $item['itemType']['name'];
				} elseif(in_array($item['flag'], $low)){
					$fit['low'][] = $item['itemType']['name'];
				} elseif($item['flag'] == 5){
					$fit['cargo'][] = $item['itemType']['name'];
				} elseif(in_array($item['flag'], $rigs)){
					$fit['rigs'][] = $item['itemType']['name'];
				} else {
					$fit['other'][] = $item['itemType']['name'];
				}
			}
			$ret['data']['fit'] = $fit;
		} elseif(strpos($link,"esi") > 0){
			//ESI Link
			$cD = $this->curllib->makeRequest('GET', $link);
			$crestData = json_decode($cD, TRUE);
			if(isset($crestData['error'])){
				$ret['errMsg'] = "There was an error, please check your link and try again. If you continue to see this error, please contact an administrator.";
				$ret['err'] = TRUE;
				return $ret;
			}
			if( isset( $crestData['killmail_id'] ) ){
				$ret['data']['killID'] = $crestData['killmail_id'];
            }
			if( isset( $crestData['killmail_time'] ) ){
				$ret['data']['killTime'] = $crestData['killmail_time'];
			}

			if( isset( $crestData['solar_system_id'] ) ){
			    $ret['data']['sysID'] = $crestData['solar_system_id'];
			    $sysData = $this->getSystem($crestData['solar_system_id']);
			    if ( isset($sysData['solarSystemName']) ) {
			        $ret['data']['sysName'] = $sysData['solarSystemName'];
			    }
			}

			if( isset( $crestData['victim'] ) ){
				$ret['data']['vicID'] = $crestData['victim']['character_id'];
				$charData = $this->getCharacter($crestData['victim']['character_id']);
				if ( isset($charData['name']) ) {
				    $ret['data']['vicName'] = $charData['name'];
                }
				$ret['data']['vicShipTypeID'] = $crestData['victim']['ship_type_id'];
				$shipData = $this->getItem($crestData['victim']['ship_type_id']);
				if ( isset($sysData['solarSystemName']) ) {
			     $ret['data']['sysName'] = $sysData['solarSystemName'];
				}


				$ret['data']['vicCorpID'] = $crestData['victim']['corporation_id'];
				$corpData = $this->getCorporation($crestData['victim']['corporation_id']);
				if (isset($corpData['name'])) {
				  $ret['data']['vicCorpName'] = $corpData['name'];
				}

				$ret['data']['damageTaken'] = $crestData['victim']['damage_taken'];
			}

			if( isset( $crestData['attackers'] ) ){
			    $attackers = $crestData["attackers"];
			    $ret['data']['numAttackers'] = count($attackers);
			    $attackerArr = array();
			    $ret['data']['attackerArr'] = $attackerArr;
			}
			/*
			$i = 0;
			if(count($attackers) > 0){
				foreach($attackers as $a){
					$alliance = '';
					if(isset($a['alliance_id'])){
						$allianceData = $this->getAlliance($a['alliance_id']);
						if(isset($allianceData['alliance_name'])){
							$alliance = $allianceData['alliance_name'];
						} else {
							$alliance = "Unknown";
						}
						
					}
					if(isset($a['ship_type_id'])){
						$shipData = $this->getItem($a['ship_type_id']);
						if(isset($shipData['typeName'])){
							$shiptype = $shipData['typeName'];
						} else {
							$shiptype = "Unknown";
						}
						
					} else {
						$shiptype = "Unknown";
					}
					if(isset($a['character_id'])){
						$charData = $this->getCharacter($a['character_id']);
						if(isset($charData['name'])){
							$character = $charData['name'];
						} else {
							$character = "Unknown";
						}	
					} else {
						$character = 'Unknown '.$i;
					}
					if(isset($a['weapon_type_id'])){
						$weaponData = $this->getItem($a['weapon_type_id']);
						if(isset($weaponData['typeName'])){
							$weaponType = $weaponData['typeName'];
						} else {
							$weaponType = "Unknown";
						}
					} else {
						$weaponType = "Unknown";
					}
					if(isset($a['corporation_id'])){
						$corpData = $this->getCorporation($a['corporation_id']);
						if(isset($corpData['corporation_name'])){
							$corporation = $corpData['corporation_name'];
						} else {
							$corporation = "Unknown";
						}
					} else {
						$corporation = "Unknown Corporation";
					}
					$attackerArr[$character] = array('corporation' => $corporation,
													'alliance' => $alliance,
													'shipType' => $shiptype,
													'weaponType' => $weaponType,
													'damageDone' => $a['damage_done']);
					$i++;
				}

				$ret['data']['attackerArr'] = $attackerArr;
			} else {
				$errid = uniqid();
				$ret['err'] = TRUE;
				$ret['errMsg'] = "Something went wrong and it was probably CCP's fault. Try again later. If it still doesn't work, give this to kilgarth: " . count($attackers) . " | ".$errid;
				$fn = $errid.".txt";
				file_put_contents($fn, sprintf("%s: %s",$link,$cD));
				return $ret;
			}
			*/

			foreach($crestData['victim']['items'] as $item){
				$itemData = $this->getItem($item['item_type_id']);
				if(in_array($item['flag'], $high)){
					$fit['high'][] = $itemData['typeName'];
				} elseif(in_array($item['flag'], $med)){
					$fit['med'][] = $itemData['typeName'];
				} elseif(in_array($item['flag'], $low)){
					$fit['low'][] = $itemData['typeName'];
				} elseif($item['flag'] == 5){
					$fit['cargo'][] = $itemData['typeName'];
				} elseif(in_array($item['flag'], $rigs)){
					$fit['rigs'][] = $itemData['typeName'];
				} else {
					$fit['other'][] = $itemData['typeName'];
				}
			}
			$ret['data']['fit'] = $fit;
		} else {
			//Invalid link
			$ret['err'] = TRUE;
			$ret['errMsg'] = "Invalid link provided.";
			return $ret;
		}

		return $ret;
	}

	function getCharacter($charID) {
		$data = $this->curllib->makeRequest('GET', sprintf('https://esi.evetech.net/v5/characters/%s/?datasource=tranquility&language=en-us',$charID));
		$dataArray = json_decode($data, TRUE);
		if(count($dataArray) > 0){
			return $dataArray;
		} else {
			return FALSE;
		}
	}

	function getCorporation($corpID) {
		$data = $this->curllib->makeRequest('GET', sprintf('https://esi.evetech.net/v5/corporations/%s/?datasource=tranquility&language=en-us',$corpID));
		$dataArray = json_decode($data, TRUE);
		$allianceID = 0;
		if(count($dataArray) > 0){
			// Check if we know about this corp already
			$exists = FALSE;
			$dbChk = $this->db->where("corpID", $corpID)->get("corporations");
			if($dbChk->num_rows() > 0) {
				if(isset($dataArray["alliance_id"])){
					$allianceID = $dataArray["alliance_id"];
				}
				if($dbChk->row(0)->allianceID == $allianceID) {
					$exists = TRUE;
				}
			}
			if(!$exists){
				// We don't have it, so store it
				$dti = array(
					"corpID" => $corpID,
					"corpName" => $dataArray["name"],
					"allianceID" => $allianceID,
					"ticker" => $dataArray["ticker"]
				);
				$this->db->replace("corporations", $dti);
			}
			return $dataArray;
		} else {
			return FALSE;
		}
	}

	function getAlliance($allianceID) {
		$data = $this->curllib->makeRequest('GET', sprintf('https://esi.evetech.net/v4/alliances/%s/?datasource=tranquility&language=en-us',$allianceID));
		$dataArray = json_decode($data, TRUE);
		if(count($dataArray) > 0){
			// Check if we know about this corp already
			$exists = FALSE;
			$dbChk = $this->db->where("allianceID", $allianceID)->get("alliances");
			if($dbChk->num_rows() > 0) {
				$exists = TRUE;
			}
			if(!$exists){
				// We don't have it, so store it
				$dti = array(
					"allianceID" => $allianceID,
					"allianceName" => $dataArray["name"],
					"ticker" => $dataArray["ticker"]
				);
				$this->db->replace("alliances", $dti);
			}
			return $dataArray;
		} else {
			return FALSE;
		}
	}

	function getItem($itemID) {
		$ret = array();
		$data = $this->db->where("typeID", $itemID)->get("invTypes");
		if($data->num_rows() > 0){
			$ret['typeID'] = $data->row(0)->typeID;
			$ret['groupID'] = $data->row(0)->groupID;
			$ret['typeName'] = $data->row(0)->typeName;
			$ret['marketGroupID'] = $data->row(0)->marketGroupID;

			return $ret;
		} else {
			return FALSE;
		}
	}

	function getSystem($sysID){
		$ret = array();
		$data = $this->db->where("solarSystemID", $sysID)->get("mapSolarSystems");
		if($data->num_rows() > 0){
			$ret['regionID'] = $data->row(0)->regionID;
			$ret['constellationID'] = $data->row(0)->constellationID;
			$ret['solarSystemName'] = $data->row(0)->solarSystemName;

			return $ret;
		} else {
			return FALSE;
		}
	}
}
