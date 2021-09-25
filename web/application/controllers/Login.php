<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

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
	public function index()
	{
		$this->load->model('User_model');

		$vars = array();
		$vars['err'] = FALSE;
		$vars['logged_in'] = FALSE;
		$vars['user'] = "";
		$vars['isReim'] = 0;
		$vars['isReimDir'] = 0;
		$vars['inCapSwarm'] = 0;
		$vars['isCapDir'] = 0;
		$vars['isBanned'] = 0;
		$vars['groupData'] = array();

		$this->form_validation->set_rules('user', 'User', 'trim|required|xss_clean|strtolower');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
		if($this->form_validation->run() == FALSE) {
			$this->load->view('header');
			$this->load->view('home');
			$this->load->view('footer');
		} else {
			$user = $this->input->post('user');
			$password = $this->input->post('password');
			$userData = $this->User_model->check_login($user,$password);
			$udLog = json_encode($userData);
			log_message("debug", "Attempting login for $user: $udLog");

			if ($userData['err'] == FALSE) {

				$banChk = $this->db->where('banEnd >', date('Y-m-d H:i:s'))->where('userName', $user)->get('bannedUsers');
				if($banChk->num_rows() > 0){
					$vars['isBanned'] = 1;
					$vars['banReason'] = $banChk->row(0)->reason;
					$vars['banEnd'] = $banChk->row(0)->banEnd;

				} else {
					$vars['isBanned'] = 0;

				}
				$vars['logged_in'] = TRUE;
				$vars['user'] = $user;
				$vars['isReim'] = $userData['isReim'];
				$vars['isReimDir'] = $userData['isReimDir'];
				$vars['inCapSwarm'] = $userData['inCapSwarm'];
				$vars['isCapDir'] = $userData['isCapDir'];
				if(isset($userData['groupData'])){
					$vars['groupData'] = $userData['groupData'];
				}

				$ldata = array(	'user'	=> $user,
								'type'	=> 'LOGIN',
								'data'	=> 'IP : ' . $this->input->ip_address());
				$this->db->insert('ulog', $ldata);

			} else {
				$dti = array('user' => $user, 'data' => "FAILED LOGIN ATTEMPT.<br>REASON: " . $userData['errReason'] . "<br>MESSAGE: " . $userData['errMessage'], 'type' => 'FAILED LOGIN');
				$this->db->insert('ulog', $dti);
				if($userData['err'] == 'GROUPBAN'){
					$vars = array_merge($vars,$userData);
				} else {
					$vars['err'] = $userData['err'];
					$vars['errMessage'] = $userData['errMessage'];
				}
			}
		}
		$this->session->set_userdata('vars',$vars);
		redirect('home');
	}
	public function logout()
	{
		$this->session->sess_destroy();
		redirect('home');
	}

	function register(){
		if($this->config->item("ALLOW_REGISTRATION")){
			$this->load->model('User_model');
			$user = $this->input->post("user", TRUE);
			$password = $this->input->post('password', TRUE);

			$ret = $this->User_model->registerUser($user, $password);

			if($ret['message']){
				echo "Account successfully created, you may now login.";
			} else {
				echo $ret['err'];
			}
		} else {
			echo "You are fucking stupid.";
		}
	}
}
