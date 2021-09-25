<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends CI_Controller {

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
		$this->vars = $this->session->userdata('vars');
		if($this->vars['logged_in']){
			$this->logged_in = TRUE;
		} else {
			redirect('notauth');
		}
	}

	function index(){
		if(isset($this->vars['repDateFil'])){
			$date = $this->vars['repDateFil'];
		} else {
			$date = date('Y-m');
		}
		$dateStr = strtotime($date);
		$data['date'] = date('F, Y', $dateStr);
		$reimCap = $this->db->where('name','ptCap')->get('adminSettings');
		$ptPayoutId = $this->db->where('name', 'ptCapPayoutIds')->get('adminSettings');
		if($ptPayoutId->num_rows() > 0){
			$ptPID = $ptPayoutId->row(0)->value;
		} else {
			$this->db->insert("adminSettings", array('name' => 'ptCapPayoutIds', 'value' => 1));
			$ptPID = 1;
		}
		$ptPID = explode(",",$ptPID);
		$data['ptCap'] = $reimCap->row(0)->value;
		$data['byUser'] = $this->db->select('k.submittedBy, COUNT(*) AS count, SUM(pc.payoutAmount) AS total')
									->from('kills k')
									->join('paymentsCompleted pc', 'pc.killID = k.killID', 'left')
									->where('k.paid','1')
									->like('k.killTime', $date, 'after')
									->group_by('submittedBy')
									->order_by('submittedBy')
									->get();
		$data['byShipType'] = $this->db->select('k.shipName, pt.typeName, COUNT(*) AS count, SUM(pc.payoutAmount) AS total')
									->from('kills k')
									->join('paymentsCompleted pc', 'pc.killID = k.killID', 'left')
									->join('payoutTypes pt', 'pt.id = pc.payoutType', 'left')
									->where('k.paid','1')
									->like('k.killTime', $date, 'after')
									->group_by('shipName, typeName')
									->order_by('shipName, typeName')
									->get();
		$data['byCorp'] = $this->db->select('k.corpName, COUNT(*) AS count, SUM(pc.payoutAmount) AS total')
									->from('kills k')
									->join('paymentsCompleted pc', 'pc.killID = k.killID', 'left')
									->where('k.paid','1')
									->like('k.killTime', $date, 'after')
									->group_by('corpName')
									->order_by('corpName')
									->get();
		$data['byRegion'] = $this->db->select('k.regName, COUNT(*) AS count, SUM(pc.payoutAmount) AS total')
									->from('kills k')
									->join('paymentsCompleted pc', 'pc.killID = k.killID', 'left')
									->where('k.paid','1')
									->like('k.killTime', $date, 'after')
									->group_by('regName')
									->order_by('regName')
									->get();
		$data['capByUser'] = $this->db->select('k.submittedBy, COUNT(*) AS count, SUM(pc.payoutAmount) AS total')
									->from('kills k')
									->join('paymentsCompleted pc', 'pc.killID = k.killID', 'left')
									->where('k.paid','1')
									->where_in('pc.payoutType', $ptPID)
									->like('killTime', $date, 'after')
									->group_by('submittedBy')
									->order_by('submittedBy')
									->get();
		$data['byType'] = $this->db->select('pt.typeName, SUM(pc.payoutAmount) AS total')
									->from('paymentsCompleted pc')
									->join('payoutTypes pt', 'pt.id = pc.payoutType')
									->like('pc.timestamp', $date, 'after')
									->group_by('typeName')
									->order_by('total')
									->get();
		$data['payoutsByUser'] = $this->db->select('paidBy, COUNT(*) AS count, SUM(payoutAmount) AS total')
									->like('timestamp', $date, 'after')
									->group_by('paidBy')
									->order_by('count', 'DESC')
									->get('paymentsCompleted');
		$data['allPayouts'] = $this->db->where('paid', '1')->limit('2500')->order_by('killTime', 'DESC')->get('vwkills');
		$this->load->view('header');
		$this->load->view('reports', $data);
		$this->load->view('footer');
	}

	function setDate(){
		$date = $this->input->post('date', TRUE);
		$vars = $this->vars;
		$vars['repDateFil'] = $date;
		$this->session->unset_userdata('vars');
		$this->session->set_userdata('vars',$vars);
	}

	function insurance() {
		$insData = $this->db->select("it.typeName,
		                             i.basicCost,
		                             i.basicPayout,
		                             i.standardCost, 
		                             i.standardPayout,
		                             i.bronzeCost,
		                             i.bronzePayout,
		                             i.silverCost,
		                             i.silverPayout,
		                             i.goldCost,
		                             i.goldPayout,
		                             i.platinumCost,
		                             i.platinumPayout")
							->from("shipInsurance i")
							->join("invTypes it", "it.typeID = i.typeID")
							->get();
		$this->load->view("header");
		$this->load->view("insurance", array("insData" => $insData));
		$this->load->view("footer");
	}
}
