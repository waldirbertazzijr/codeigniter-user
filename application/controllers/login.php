<?php
class Login extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		// Load the Library
		$this->load->library('user');
        $this->load->helper('url');

	}
	
	function index()
	{
		// If user is already logged in, send it to main
		$this->user->on_valid_session('main');
		
		// Loads the login view
		$this->load->view('login');
	}
	
	function validate()
	{
		// Receives the login data
		$login = $this->input->post('login');
		$password = $this->input->post('password');
		
		/* 
		 * Validates the user input
		 * The user->login returns true on success or false on fail.
		 * It also creates the user session.
		*/
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
		// Remove user session.
		$this->user->destroy_user();
		
		// Bye, thanks! :)
		$this->session->set_flashdata('success_message', 'You are now logged out.');
		redirect('login');
	}
}
?>
