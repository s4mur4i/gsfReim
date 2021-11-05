<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

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

	function getInsurance(){
		$data = $this->curllib->makeRequest('GET', 'https://esi.evetech.net/v1/insurance/prices/?datasource=tranquility&language=en-us');
		$dataArray = json_decode($data, TRUE);
		if(count($dataArray) > 0){
			//We have data to work with, process it. First thing we're going to do is nuke the old data, this is unimportant if it gets fucked and lost.
			$this->db->truncate("shipInsurance");

			foreach($dataArray as $type){
				$typeID = $type['type_id'];
				$levels = $type['levels'];
				$dti = array(
					    "typeID"	=>	$typeID,
					);
				foreach($levels as $level){
					switch($level['name']){
						case "Basic":
							$dti["basicCost"] = $level['cost'];
							$dti['basicPayout'] = $level['payout'];
							break;
						case "Standard":
							$dti["standardCost"] = $level['cost'];
							$dti['standardPayout'] = $level['payout'];
							break;
						case "Bronze":
							$dti["bronzeCost"] = $level['cost'];
							$dti['bronzePayout'] = $level['payout'];
							break;
						case "Silver":
							$dti["silverCost"] = $level['cost'];
							$dti['silverPayout'] = $level['payout'];
							break;
						case "Gold":
							$dti["goldCost"] = $level['cost'];
							$dti['goldPayout'] = $level['payout'];
							break;
						case "Platinum":
							$dti["platinumCost"] = $level['cost'];
							$dti['platinumPayout'] = $level['payout'];
							break;
						default:
							break;
					}
				}
				$this->db->insert("shipInsurance", $dti);
			}
		}
	}

	function getAlliances() {
		/*
			This function is going to grab a list of unique alliance ID's from the corporations table and then make sure that we have them in the database. 
		*/
		$sql = "SELECT DISTINCT(allianceID) FROM corporations WHERE allianceID IS NOT NULL";
		$corpData = $this->db->query($sql);

		if($corpData->num_rows() > 0){
			//We have some data, lets loop and process this shit
			foreach($corpData->result() as $row){
				//We're going to be lazy and just use REPLACE INTO's here. Its less work than a SELECT and then UPDATE/INSERT.
				$data = $this->curllib->makeRequest('GET', sprintf('https://esi.evetech.net/v4/alliances/%s/?datasource=tranquility&language=en-us',$row->allianceID));
				$dataArray = json_decode($data, TRUE);
				if(count($dataArray) > 0){
					$dti = array(
					             "allianceID"	=> $row->allianceID,
					             "allianceName"	=>	$dataArray['name'],
					             "ticker"	=>	$dataArray['ticker']
					             );
					$this->db->replace("alliances", $dti);
				}
			}
		}

	}

	function propagateCorporations() {
		/*
			This function is to get a list of all corporations from kills that have been submitted and store them in the corporations table.
			The corporation information will be stored going forward when kills are submitted, but we need a solution to go back and propagate.
		*/
		$sql = "SELECT DISTINCT(corpID) FROM kills";
		$corpData = $this->db->query($sql);

		if($corpData->num_rows() > 0){
			//We have a list of distinct corp ID's, lets loop this bitch and pull the pub data from CCP
			foreach($corpData->result() as $row){
				$data = $this->curllib->makeRequest('GET', sprintf('https://esi.evetech.net/v5/corporations/%s/?datasource=tranquility&language=en-us',$row->corpID));
				$dataArray = json_decode($data, TRUE);
				if(count($dataArray) > 0){
					if(isset($dataArray['alliance_id'])){
						$allianceID = $dataArray['alliance_id'];
					} else {
						$allianceID = NULL;
					}
					$dti = array(
					             "corpID"	=> $row->corpID,
					             "corpName"	=>	$dataArray['name'],
					             "allianceID"	=>	$allianceID,
					             "ticker"	=>	$dataArray['ticker']
					             );
					$this->db->replace("corporations", $dti);
				}
			}
		}
	}
}