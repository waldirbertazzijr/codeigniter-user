<?php
class Main extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		
		$this->load->library('user');
		$this->load->helper('url');
		
		/*
		 * This function prevents any method on this
		 * controller to be executed if the user isnt
		 * logged in properly.
		 */
		$this->user->on_invalid_session('login');
	}
	
	/*
	 * If the user can reach this function its logged in.
	 */
	function index(){
		
		// Show the welcome screen.
		$this->load->view('home');
	}

    function add_user(){
        $this->load->library('user_manager');

        var_dump($this->user_manager->save_user('Testando', 'teste', 'teste'));
    }

    function add_permission_user(){
        $this->load->library('user_manager');
        var_dump($this->user_manager->add_permission(2, 1));
    }

    function new_permission(){
        $this->load->library('user_manager');
        var_dump($this->user_manager->save_permission('Testing', 'A testing permission'));
    }
}
?>
