<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

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
		$this->auth_method = $this->config->item("AUTH_METHOD");
		$this->logged_in = FALSE;
		$this->isReim = FALSE;
		$this->isReimDir = FALSE;
		$this->vars = $this->session->userdata('vars');
	 	if($this->vars['logged_in'] == TRUE){
	 		$this->logged_in = TRUE;
			if($this->vars['isReim'] == 1){
		 		$this->isReim = TRUE;
		 	}
			if($this->vars['isReimDir'] == 1){
		 		$this->isReimDir = TRUE;
		 	}
	 	}
		
		if(!$this->isReim && !$this->isReimDir){
			redirect('notauth');
		}	
	}

	function userMgmt(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$data['users'] = $this->db->select("user, active, id")->get('users');
				$this->load->view('header');
				$this->load->view('admin/userMgmt', $data);
				$this->load->view('footer');
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	
	function activateUser(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$this->db->where("id", $this->input->post('userID', TRUE))->update('users', array("active" => 1));
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	function deactivateUser(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$this->db->where("id", $this->input->post('userID', TRUE))->update('users', array("active" => 0));
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	function editUser(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$uid = xss_clean($this->uri->segment(3));
				$data['user'] = $this->db->where('id', $uid)->get('users');
				
				$this->load->view('header');
				$this->load->view('admin/editUser', $data);
				$this->load->view('footer');
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	function editGroups(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$isReim = $this->input->post("isReim", TRUE);
				$isReimDir = $this->input->post("isReimDir", TRUE);
				$inCapswarm = $this->input->post("inCapswarm", TRUE);
				$uid = $this->input->post('userID', TRUE);
				
				$groups = array();
				
				if($isReim == 1){$groups[] = 1;}
				if($isReimDir == 1){$groups[] = 2;}
				if($inCapswarm == 1){$groups[] = 3;}
				
				$gids = implode(",", $groups);
				
				$this->db->where('id', $uid)->update('users', array("gids" => $gids));
				
				echo "User groups updated.";
				
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	function resetPassword(){
		if($this->auth_method == "INTERNAL"){
			if($this->isReimDir){
				$uid = $this->input->post('userID', TRUE);
				$password = $this->input->post('password');
				
				$this->db->where('id', $uid)->update('users', array('password' => sha1($password)));
				
				echo "Password updated.";
			} else {
				show_error("You are not authorized to view this page.", 403);
			}
		} else {
			show_error("The authentication method is not set to internal.", 500);
		}
	}
	function regionMgmt() {
		if($this->isReimDir){
			$data['regions'] = $this->db->order_by('regName', 'ASC')->get('ptRegions');
			
			$this->load->view('header');
			$this->load->view('admin/regionMgmt', $data);
			$this->load->view('footer');
		} else {
			redirect('notauth');
		}	
	}
	
	function addRegion(){
		if($this->isReimDir){
			$regName = $this->input->post('regName', TRUE);
			$regChk = $this->db->where('reg_name', $regName)->get('vwsysconreg');
			if($regChk->num_rows() > 0){
				$regID = $regChk->row(0)->reg_id;
				$ptChk = $this->db->where('regID', $regID)->get('ptRegions');
				if($ptChk->num_rows() == 0){
					$dti = array('regID' => $regID, 'regName' => $regChk->row(0)->reg_name);
					$this->db->insert('ptRegions',$dti);
					
					$dti = array('user' => $this->vars['user'], 'type' => 'ADD REGION', 'data' => "ADDED REGION " . $regChk->row(0)->reg_name);
					$this->db->insert('ulog', $dti);
				} else {
					echo $regName . " is already in the peacetime list.";
				}
			} else {
				echo "I could not find a region called '" . $regName . "', please check the name and try again.";
			}
		}
	}
	
	function delRegion(){
		if($this->isReimDir){
			$regID = $this->input->post('regID', TRUE);
			$ptChk = $this->db->where('regID', $regID)->get('ptRegions');
			if($ptChk->num_rows() > 0){
				$this->db->where('regID', $regID)->delete('ptRegions');
				
				$dti = array('user' => $this->vars['user'], 'type' => 'DEL REGION', 'data' => "DELETED REGION " . $ptChk->row(0)->regName);
				$this->db->insert('ulog', $dti);
			} else {
				echo "I could not find the region specified.";
			}
		}
	}
	
	function payoutMgmt(){
		if($this->isReimDir){
			$data['payouts'] = $this->db->order_by('typeName', 'ASC')->get('vwpayouts');
			$payoutTypes = $this->db->where('active', '1')->get('payoutTypes');
			$data['payoutTypes'] = array('0' => 'Select Payout Type');
			if($payoutTypes->num_rows() > 0){
				foreach($payoutTypes->result() as $row){
					$data['payoutTypes'][$row->id] = $row->typeName;
				}
			}
			$this->load->view('header');
			$this->load->view('admin/payoutMgmt', $data);
			$this->load->view('footer');
		} else {
			redirect('notauth');
		}
	}
	
	function payoutTypes(){
		if($this->isReimDir){
			$data['types'] = $this->db->get('payoutTypes');
			
			$this->load->view('header');
			$this->load->view('admin/payoutTypes', $data);
			$this->load->view('footer');
		} else {
			redirect('notauth');
		}
	}
	
	function addPayoutType(){
		if($this->isReimDir){
			$typeName = $this->input->post('typeName', TRUE);
			$prefix = $this->input->post('prefix', TRUE);
			
			$ptChk = $this->db->where('typeName', $typeName)->or_where('prefix', $prefix)->get('payoutTypes');
			if($ptChk->num_rows() == 0){
				$dti = array('typeName' => $typeName, 'active' => '1', 'prefix' => $prefix);
				$this->db->insert('payoutTypes', $dti);
				
				$dti = array('user' => $this->vars['user'], 'type' => 'ADD PAYOUT TYPE', 'data' => "ADDED PAYOUT TYPE " . $typeName);
				$this->db->insert('ulog', $dti);
			} else {
				echo "Something already exists with either the name '" . $typeName . "' or the prefix '" . $prefix."'";
			}
		}
	}
	
	function deactivatePayoutType(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$ptChk = $this->db->where('id', $id)->get('payoutTypes');
			if($ptChk->num_rows() > 0){
				$dtu = array('active' => '0');
				$this->db->where('id', $id)->update('payoutTypes', $dtu);
				
				$dti = array('user' => $this->vars['user'], 'type' => 'DEACTIVATE PAYOUT TYPE', 'data' => "DEACTIVATED PAYOUT TYPE " . $ptChk->row(0)->typeName);
				$this->db->insert('ulog', $dti);
			} else {
				echo "I could not find a matching payout type.";
			}
		}
	}
	
	function activatePayoutType(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$ptChk = $this->db->where('id', $id)->get('payoutTypes');
			if($ptChk->num_rows() > 0){
				$dtu = array('active' => '1');
				$this->db->where('id', $id)->update('payoutTypes', $dtu);
				
				$dti = array('user' => $this->vars['user'], 'type' => 'ACTIVATE PAYOUT TYPE', 'data' => "ACTIVATED PAYOUT TYPE " . $ptChk->row(0)->typeName);
				$this->db->insert('ulog', $dti);
			} else {
				echo "I could not find a matching payout type.";
			}
		}
	}
	
	function addPayout(){
		if($this->isReimDir){
			$typeName = $this->input->post('typeName', TRUE);
			$payoutType = $this->input->post('payoutType', TRUE);
			$payoutAmount = $this->input->post('payoutAmount', TRUE);
			
			$dbChk = $this->db->where('typeName', $typeName)->get('invTypes');
			if($dbChk->num_rows() > 0){
				$typeID = $dbChk->row(0)->typeID;
				$poChk = $this->db->where('typeID', $typeID)->where('payoutType', $payoutType)->get('payouts');
				if($poChk->num_rows() == 0){
					$dti = array('typeID' => $typeID,
								'typeName' => $dbChk->row(0)->typeName,
								'payoutType' => $payoutType,
								'payoutAmount' => $payoutAmount);
					$this->db->insert('payouts', $dti);
				} else {
					echo "The item '" . $typeName . "' already has an existing payout, if you wish to change it please click 'edit' next to the item.";
				}
			} else {
				echo "I could not find any items matching '" . $typeName . "'";
			}
		}
	}
	
	function payoutDetail(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$poChk = $this->db->where('id', $id)->get('vwpayouts');
			if($poChk->num_rows() > 0){
				$data['typeName'] = $poChk->row(0)->typeName;
				$data['payoutType'] = $poChk->row(0)->payoutType;
				$data['payoutAmount'] = $poChk->row(0)->payoutAmount;
				$data['payoutTypeID'] = $poChk->row(0)->payoutTypeID;
				$data['id'] = $id; 
				
				$this->load->view('admin/payoutDetail', $data);
			} else {
				echo "I could not find the item specified, please try again. If the problem persists, please contact an administrator.";
			}
		}
	}
	
	function editPayout(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$payoutAmount = $this->input->post('payoutAmount', TRUE);
			$payoutTypeID = $this->input->post('payoutTypeID', TRUE);
			
			$poChk = $this->db->where('id', $id)->where('payoutType', $payoutTypeID)->get('payouts');
			if($poChk->num_rows() > 0){
				$dtu = array('payoutAmount' => $payoutAmount);
				$this->db->where('id', $id)->update('payouts', $dtu);
			} else {
				echo "I could not find the item specified, please try again. If the problem persists, please contact an administrator.";
			}
		}
	}
	
	function delPayout(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$poChk = $this->db->where('id', $id)->get('payouts');
			if($poChk->num_rows() > 0){
				$this->db->where('id', $id)->delete('payouts');
			} else {
				echo "I could not find the item specified, please try again. If the problem persists, please contact an administrator.";
			}
		}
	}
	
	function payLoss(){
		if($this->isReim || $this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			$killData = $this->db->where('killID', $killID)->get('kills');
			
			if($killData->num_rows() > 0){
				if($this->config->item("getStats")){
					$this->load->model("User_model");
					$loginStats = $this->User_model->getLoginStats($killData->row(0)->submittedBy);
					if(isset($loginStats['participation'])) {
						$mumbleDate = $loginStats['lastMumble'];
						$mumbleDiff = $this->dateDiff(date("Y-m-d"), $loginStats['lastMumble']);
						$jabberDate = $loginStats['lastJabber'];
						$jabberDiff = $this->dateDiff(date("Y-m-d"), $loginStats['lastJabber']);
						$forumsDate = $loginStats['lastActive'];
						$forumsDiff = $this->dateDiff(date("Y-m-d"), $loginStats['lastActive']);
						$thirtyDayPap = $loginStats['participation']['30day'];
						$sixtyDayPap = $loginStats['participation']['60day'];
						$ninetyDayPap = $loginStats['participation']['90day'];
					} else {
						$mumbleDate = "Unknown";
						$mumbleDiff = 0;
						$jabberDate = "Unknown";
						$jabberDiff = 0;
						$forumsDate = "Unknown";
						$forumsDiff = 0;
						$thirtyDayPap = 0;
						$sixtyDayPap = 0;
						$ninetyDayPap = 0;
					}
				}
				$payoutArr = unserialize($killData->row(0)->availablePayouts);
				$payoutTypes = array_keys($payoutArr);
				$payoutNames = $this->db->where_in('id', $payoutTypes)->get('payoutTypes');
				$newPayoutNamesArr = array();
				$prefixes = array();
				$overPtCap = 0;
				
				if(in_array(4,$payoutTypes)){
					$killTime = str_replace('.','-',$killData->row(0)->killTime);
					$killDate = new DateTime($killTime); 
					
					$ptQualified = 1;
					$ptCap = $this->db->where('name', 'ptCap')->get('adminSettings');
					$ptCapVal = $ptCap->row(0)->value;
					$d = $killDate->format('Y-m');
					$ptAmtChk = $this->db->where('submittedBy', $killData->row(0)->submittedBy)->where('month', $d)->get('vwptcap');
					if($ptAmtChk->num_rows() > 0 && $ptAmtChk->row(0)->total >= $ptCapVal){
						$overPtCap = 1;
					}
				}
				
				foreach($payoutNames->result() as $row){
					$newPayoutNamesArr[$row->id] = $row->typeName;
					$prefixes[$row->id] = $row->prefix;
				}
				$newPayoutArr = array('0' => 'Select Payout Type');
				foreach($payoutArr as $key => $value){
					$newPayoutArr[$key] = $newPayoutNamesArr[$key];
					$prefix = $prefixes[$key];?>
					<input type="hidden" id="<?php echo $killID . '-'.$key; ?>" value="<?php echo $value; ?>"></input>
					<input type="hidden" id="<?php echo $killID . '-'.$key.'-PRE'; ?>" value="<?php echo $prefix; ?>"></input>
				<?php }
				if($killData->row(0)->ptQualified == 1 && $overPtCap == 0){ ?>
					<span class="badge badge-success">Peacetime Qualified</span>
				<?php } elseif($killData->row(0)->ptQualified == 0) { ?>
					
				<?php } else { ?>
					<span class="badge badge-danger">NOT Peacetime Qualified</span>
				<?php }
				if($overPtCap == 1){ ?>
					<span class="badge badge-danger">OVER PEACETIME CAP</span>
				<?php } 
				$sec = $killData->row(0)->secStatus;
				if($sec <= 0.0){ ?>
					<span class="badge badge-danger">NULL SEC</span>
				<?php } elseif($sec > 0.0 && $sec < 0.5){ ?>
					<span class="badge badge-warning">LOW SEC</span>
				<?php } else { ?>
					<span class="badge badge-info">HIGH SEC</span>
				<?php }
				if($this->config->item("getStats")){ 
					if($mumbleDiff >= 0 && $mumbleDiff <= 3){
						$mum = "success";
					} elseif($mumbleDiff > 3 && $mumbleDiff <= 5){
						$mum = "warning";
					} else {
						$mum = "danger";
					}

					if($jabberDiff >= 0 && $jabberDiff <= 3){
						$jab = "success";
					} elseif($jabberDiff > 3 && $jabberDiff <= 5){
						$jab = "warning";
					} else {
						$jab = "danger";
					}

					if($forumsDiff >= 0 && $forumsDiff <= 3){
						$for = "success";
					} elseif($forumsDiff > 3 && $forumsDiff <= 5){
						$for = "warning";
					} else {
						$for = "danger";
					} ?>

					<span class="badge badge-<?php echo $mum; ?>">M: <?php echo $mumbleDate; ?></span>
					<span class="badge badge-<?php echo $jab; ?>">J: <?php echo $jabberDate; ?></span>
					<span class="badge badge-<?php echo $for; ?>">F: <?php echo $forumsDate; ?></span>
					<span class="badge badge-info">30 Day Pap: <?php echo $thirtyDayPap; ?></span>
					<span class="badge badge-info">60 Day Pap: <?php echo $sixtyDayPap; ?></span>
					<span class="badge badge-info">90 Day Pap: <?php echo $ninetyDayPap; ?></span>
				<?php 
				}
				?>
				
				<hr>
				<div class="input-group">
		      		<span class="input-group-addon">Send ISK To</span>
		      		<input type="text" class="form-control" id="victimStr" value="<?php echo $killData->row(0)->victimName; ?>"></input>
		      	</div>
		      	<br>
		      	<div class="input-group">
		      		<span class="input-group-addon">Payout Type</span>
		      		<select class="form-control" id="payoutType" onchange="getPayoutInfo(<?php echo $killID; ?>)">
		      			<?php foreach($newPayoutArr as $key => $value){ ?>
		      				<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
		      			<?php } ?>
		      		</select>
		      	</div>
		      	<br>
		      	<div class="input-group">
		      		<span class="input-group-addon">Reason</span>
		      		<input type="text" class="form-control" id="reasonStr" value=""></input>
		      	</div>
		      	<br>
		      	<div class="input-group">
		      		<span class="input-group-addon">Payout</span>
		      		<input type="text" class="form-control" id="payoutStr" value="0"></input>
		      	</div>
		      	<br>
		      	<div class="input-group">
		      		<span class="input-group-addon">Notes</span>
		      		<textarea class="form-control" cols="10" id="payoutNotes"></textarea>
		      	</div>
		      	<br>
		      	<button type="button" class="btn btn-outline-success" value="Paid" onclick="payOut(<?php echo $killID; ?>)">Paid</button>
	      	<?php
			} else {
				echo "I could not find the kill specified, please try again. If the problem persists, please contact an administrator.";
			}
		}
	}

	function payOut(){
		if($this->isReim || $this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			$payoutType = $this->input->post('payoutType', TRUE);
			$payoutAmount = $this->input->post('payoutAmount', TRUE);
			$payoutNotes = $this->input->post('payoutNotes', TRUE);
			$kChk = $this->db->where('killID', $killID)->where('paid', '0')->get('kills');
			$pChk = $this->db->where('killID',$killID)->get('paymentsCompleted');
			
			if($kChk->num_rows() > 0 && $pChk->num_rows() == 0){
				$dti = array('killID' => $killID,
							'payoutType' => $payoutType,
							'payoutAmount' => $payoutAmount,
							'payoutNotes' => $payoutNotes,
							'paidBy' => $this->vars['user']);
				$this->db->insert('paymentsCompleted',$dti);
				$dtu = array('paid' => '1');
				$this->db->where('killID', $killID)->update('kills', $dtu);
				echo "Payment completed, thanks!";
			} else {
				echo "Something went wrong, please contact an administrator. " . $killID;
			}
		}
	}
	function denyPayout(){
		if($this->isReim || $this->isReimDir){
			$reason = $this->input->post('reason',TRUE);
			$killID = $this->input->post('killID',TRUE);
			
			$kChk = $this->db->where('killID', $killID)->get('kills');
			if($kChk->num_rows() > 0){
				$dChk = $this->db->where('killID', $killID)->get('paymentsDenied');
				if($dChk->num_rows() == 0){
					$dti = array('killID' => $killID,
								'reason' => $reason,
								'deniedBy' => $this->vars['user']);
					$this->db->insert('paymentsDenied',$dti);
					$dtu = array('paid' => 2);
					$this->db->where('killID', $killID)->update('kills', $dtu);
					echo "Payout denied successfully.";
				} else {
					echo "This payout has already been denied.";
				}
			} else {
				echo "I could not find the payout you are looking for, please try again. If the problem persists, please contact an administrator.";
			}
		}
	}
	
	function viewReserved(){
		if($this->isReimDir){
			$data['reserved'] = $this->db->where('paid', 0)->where('reservedBy IS NOT NULL', null, false)->get('kills');
			
			$this->load->view('header');
			$this->load->view('admin/viewReserved', $data);
			$this->load->view('footer');
		} else {
			redirect('notauth');
		}
	}
	
	function admRelease(){
		if($this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			
			$dbChk = $this->db->where('killID', $killID)->where('paid', 0)->get('kills');
			if($dbChk->num_rows() > 0){
				$dtu = array('reservedBy' => NULL, 'reservedDate' => NULL);
				$this->db->where('killID',$killID)->update('kills',$dtu);
			} else {
				echo "I could not find the loss specified.";
			}
		}
	}
	function viewBanned(){
		if($this->isReimDir){
			$data['bannedUsers'] = $this->db->where('banEnd >', date('Y-m-d H:i:s'))->get('bannedUsers');
			
			$this->load->view('header');
			$this->load->view('admin/bannedUsers', $data);
			$this->load->view('footer');
		} else {
			redirect('notauth');
		}
	}
	
	function unbanUser(){
		if($this->isReimDir){
			$banID = $this->input->post('banID', TRUE);
			$dbChk = $this->db->where('id', $banID)->get('bannedUsers');
			if($dbChk->num_rows() > 0){
				$this->db->where('id', $banID)->delete('bannedUsers');
				$dti = array('user' => $this->vars['user'], 'type' => 'DEL BAN', 'data' => "DELETED BAN FOR " . $dbChk->row(0)->userName);
				$this->db->insert('ulog', $dti);
			} else {
				echo "I could not find the ban requested.";
			}
		}
	}
	
	function banUser(){
		if($this->isReimDir){
			$userName = $this->input->post('userName', TRUE);
			$reason = $this->input->post('reason', TRUE);
			$expiry = $this->input->post('expiry', TRUE);
			
			$bChk = $this->db->where('userName', $userName)->where('banEnd >', date('Y-m-d H:i:s'))->get('bannedUsers');
			if($bChk->num_rows() > 0){
				echo "This user is already banned until: " . $bChk->row(0)->banEnd . " for reason: " . $bChk->row(0)->reason;
			} else {
				$dti = array('userName' => $userName,
							'reason' => $reason,
							'banEnd' => $expiry);
				$this->db->insert('bannedUsers', $dti);
				$dti = array('user' => $this->vars['user'], 'type' => 'BAN USER', 'data' => "BANNED USER '" . $userName . "' UNTIL " . $expiry . " WITH REASON " . $reason);
				$this->db->insert('ulog', $dti);
				echo "The user has been banned with the reason <b>" . $reason . "</b> until " . $expiry;
			}
		}
	}
	
	function getUserAutocomplete(){
		if($this->isReimDir){
			$items = array();
			if($this->config->item("AUTH_METHOD") == "INTERNAL"){
				$geturlstr = explode('term=', $_SERVER['QUERY_STRING']);
				$item = urldecode(xss_clean($geturlstr[1]));
				$this->db->select('user');
				$this->db->distinct();
				$this->db->like('user', $item, 'after');
				$this->db->order_by('user', 'asc');
				$this->db->limit(10);
				$itemrec = $this->db->get('users');
				foreach($itemrec -> result() as $res){
					$items[] = $res->user;
				}
			} else {
				$geturlstr = explode('term=', $_SERVER['QUERY_STRING']);
				$item = urldecode(xss_clean($geturlstr[1]));
				$this->db->select('submittedBy');
				$this->db->distinct();
				$this->db->like('submittedBy', $item, 'after');
				$this->db->order_by('submittedBy', 'asc');
				$this->db->limit(10);
				$itemrec = $this->db->get('kills');
				foreach($itemrec -> result() as $res){
					$items[] = $res->submittedBy;
				}
			}
			
			echo json_encode($items);
		}
	}
	
	function getItemsAutocomplete(){
		if($this->isReimDir){
			$geturlstr = explode('term=', $_SERVER['QUERY_STRING']);
			$item = urldecode(xss_clean($geturlstr[1]));
			$this->db->select('typeName');
			$this->db->distinct();
			$this->db->like('typeName', $item, 'after');
			$this->db->order_by('typeName', 'asc');
			$this->db->limit(10);
			$itemrec = $this->db->get('invTypes');
			$items = array();
			foreach($itemrec -> result() as $res){
				$items[] = $res->typeName;
			}
			echo json_encode($items);
		}
	}
	function getRegionsAutocomplete(){
		if($this->isReimDir){
			$geturlstr = explode('term=', $_SERVER['QUERY_STRING']);
			$item = urldecode(xss_clean($geturlstr[1]));
			$this->db->select('reg_name');
			$this->db->distinct();
			$this->db->like('reg_name', $item, 'after');
			$this->db->order_by('reg_name', 'asc');
			$this->db->limit(10);
			$itemrec = $this->db->get('vwsysconreg');
			$items = array();
			foreach($itemrec -> result() as $res){
				$items[] = $res->reg_name;
			}
			echo json_encode($items);
		}
	}
	function viewDenied(){
		if($this->isReimDir || $this->isReim){
			$data['denied'] = $this->db->order_by("timestamp", "DESC")->limit(500)->get('vwdeniedPayments');
			
			$this->load->view('header');
			$this->load->view('admin/viewDenied', $data);
			$this->load->view('footer');
		}
	}
	function undenyPayout(){
		if($this->isReimDir){
			$killID = $this->input->post('killID', TRUE);
			$dbChk = $this->db->where('killID', $killID)->where('paid', 2)->get('kills');
			if($dbChk->num_rows() > 0){
				$dtu = array('reservedBy' => $this->vars['user'],
							'reservedDate' => date('Y-m-d H:i:s'),
							'paid' => 0);
				$this->db->where('killID', $killID)->update('kills', $dtu);
				$this->db->where('killID', $killID)->delete('paymentsDenied');
			} else {
				echo "Something went wrong, I could not find " . $killID;
			}
		}
	}
	
	function viewSettings(){
		if($this->isReimDir){
			$data['settings'] = $this->db->get('adminSettings');
			
			$this->load->view('header');
			$this->load->view('admin/viewSettings', $data);
			$this->load->view('footer');
		} else {
			redirect('nothauth');
		}
	}
	function editSetting(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			$value = $this->input->post('value', TRUE);
			
			$dbChk = $this->db->where('id', $id)->get('adminSettings');
			if($dbChk->num_rows() > 0){
				$dtu = array('value' => $value);
				$this->db->where('id', $id)->update('adminSettings', $dtu);
				echo "Setting Saved Successfully";
			} else {
				echo "There was an error, the setting could not be found";
			}
		}
	}
	function getSettingDetails(){
		if($this->isReimDir){
			$id = $this->input->post('id', TRUE);
			
			$dbChk = $this->db->where('id', $id)->get('adminSettings');
			if($dbChk->num_rows() > 0){
				$data['details'] = $dbChk;
				$this->load->view('admin/settingDetails', $data);
			} else {
				echo "There was an error, the setting could not be found";
			}
		}
	}
	function viewLog(){
		if($this->session->userdata('vars')['isReimDir']){
			$data['logdata'] = $this->db->where('type !=', 'LOGIN')->order_by('eventtimedate', 'DESC')->limit('1000')->get('ulog');
			$this->load->view('header');
			$this->load->view('admin/viewLog',$data);
			$this->load->view('footer');			
		}
	}

	function viewGroupBans(){
		if($this->session->userdata('vars')['isReimDir']){
			$data['groupBans'] = $this->db->where('active', '1')->get("bannedGroups");
			$this->load->view("header");
			$this->load->view("admin/viewGroupBans", $data);
			$this->load->view("footer");
		}
	}

	function unbanGroup() {
		$groupID = $this->input->post("banID", TRUE);
		$this->db->where('id', $groupID)->update("bannedGroups", array("active" => 0, "removedBy" => $this->session->userdata('vars')['user'], "removedOn" => date('Y-m-d h:i:s')));
	}

	function banGroup() {
		$groupName = $this->input->post("groupName");
		$reason = $this->input->post("reason");
		$bannedBy = $this->session->userdata('vars')['user'];

		$chk = $this->db->where("groupName", $groupName)->where('active', 1)->get("bannedGroups");
		if($chk->num_rows() > 0){
			echo "This group is already banned, you're fucking stupid.";
		} else {
			$dti = array(
				"groupName"		=> $groupName,
				"reason"		=> $reason,
				"bannedBy"		=> $bannedBy,
				"active"		=> 1);
			$this->db->insert("bannedGroups", $dti);

			echo "Done";
		}

	}

	function dateDiff($date_1 , $date_2 , $differenceFormat = '%a' )
	{
	    $datetime1 = date_create($date_1);
	    $datetime2 = date_create($date_2);
	    
	    $interval = date_diff($datetime1, $datetime2);
	    
	    return $interval->format($differenceFormat);
	    
	}
}