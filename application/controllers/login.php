<?php

/**
 * User Controller
 * This controller fully demonstrates the user class.
 *
 * @package User
 * @author Waldir Bertazzi Junior
 * @link http://waldir.org/
 **/
class Login extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		// Load the Library
		$this->load->library(array('user', 'user_manager'));
        $this->load->helper('url');

	}
	
	function index()
	{		
		// If user is already logged in, send it to main
		$this->user->on_valid_session('login/private_page');
		
		// Loads the login view
		$this->load->view('login');
	}
	
	function private_page(){
		// if user tries to direct access it will be sent to index
		$this->user->on_invalid_session('login');
		
		// ... else he will view home
		$this->load->view('home');
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
			redirect('login/private_page');
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
