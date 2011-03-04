<?php
class Main extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		$this->load->library('user');
		$this->load->helper('url');
		
		// This will send users back to login if their session
		// isnt valid.
		$this->user->on_invalid_session('login');
	}
	
	// If the user can get to the functions its certanly
	// logged in because we put the redirect on invalid
	// on our controller. Every time this class is
	// created, it will run.
	function index(){
		
		// Load the welcome screen.
		$this->load->view('home');
	}
}
?>
