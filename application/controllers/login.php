<?php
class Login extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		$this->load->library('user');
		$this->load->helper('url');
		
		// For sake of simplicity we put the login
		// actions inside this class.
		// Its much better if you separate them.
		
		// Thats why we need to check the segment.
		// If its not a logout action we send it to
		// main if we can find the sessions
		if($this->uri->segment(2) != "logout")
			$this->user->on_valid_session('main');
	}
	
	function index()
	{
		// Loads the login view
		$this->load->view('login');
	}
	
	function validate()
	{
		// Receives the login data
		$login = $this->input->post('login');
		$password = $this->input->post('password');
		
		// Validates the user input
		// The user->login returns true on success or false on fail.
		// It also creates the user session.
		if($this->user->login($login, $password)){
			// Success
			redirect('main');
		} else {
			// Oh, holdon sir.
			$this->session->set_flashdata('error_message', 'Invalid login or password.');
			redirect('login');
		}
	}
	
	// Simple logout function
	function logout()
	{
		// This function will destroy every trace of the logged
		// user on our system.
		$this->user->destroy_user();
		
		// Bye, thanks! :)
		$this->session->set_flashdata('success_message', 'Logout realizado com sucesso.');
		redirect('login');
	}
}
?>
