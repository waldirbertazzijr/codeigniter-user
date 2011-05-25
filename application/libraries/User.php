<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User Class
 *
 * @package		Orion Project
 * @subpackage	Libraries
 * @category	Users
 * @author		Waldir Bertazzi Junior
 * @link		http://waldir.org/orion/
 */

/* 
 * This constant is used to make the login method
 * dont update the last login on database
 * This is only for optmization pruposes.
 *
 */
define('DONT_UPDATE_LOGIN', false);


class CI_User {
	
	public $user_data 				= array();
	protected $CI;

	/**
	 * Constructor
	 * 
     * Loads the session library witch is essential for
     * us. Also gets a instance of CI class.
	 * 
	 */
	function __construct(){
		$this->CI =& get_instance();
        
        // load session library
        $this->CI->load->library('session');
	}
	
	/**
	 * 
	 * On Invalid Session - Simple redirect if the user is not
	 * already logged in.
	 * 
	 * @param string $destiny - the destiny if the user is not logged in
	 * 
	 */
	function on_invalid_session($destiny){
		if(!$this->validate_session()){
			$this->CI->session->set_flashdata('error_message', 'Invalid session.');
			redirect($destiny);
		}
	}
	
	/**
	 * On Valid Session - Simple redirect the user
	 * if its already logged in.
	 * 
	 * @param string $destiny - the destiny if the user is logged in
	 *
	 */
	function on_valid_session($destiny){
        if($this->validate_session()) {
	        // if its not logged we must clear the flashdata because it was filled on validate
            $this->CI->session->set_flashdata('error_message', '');
            redirect($destiny);
        }
	}
	
	/**
	 * Validate Session - Return true if the session stills valid
     * otherwise returns false.
	 * 
	 * @return boolean
	 */
	function validate_session(){
        // This function doesnt need to update the last_login on database.
		if($this->login($this->CI->session->userdata('login'), $this->CI->session->userdata('pw'), DONT_UPDATE_LOGIN)){
			return true;
		}
		return false;
	}
	
	/**
	 * Login - Receives the user and the password, verifies it
	 * and create a new session.
	 * 
	 * @param string $login - The login to validate 
	 * @param string $password - The password to validate
	 */
	function login($login, $password, $update_last_login = true){
		$user_query = $this->CI->db->get_where('users', array('login'=>$login, 'password'=>md5($password)));
		if($user_query->num_rows()==1){
            // Loggin the user in...

			// save the user data
            $this->user_data = $user_query->row();

            //loads the user permissions
            $this->load_permission($this->user_data->id);

            // create the user session
			$this->create_session($login, $password);
			
			// updates last login if needed
			if($update_last_login){
                $this->update_last_login();
            }

			return true;
        } else {
            // Invalid credentials
			return false;
		}
	}
	
	/**
	 * Create session - creates the session with valid data
	 * its used by the validate function.
	 * 
	 * @param string $login - The login to save
	 * @param string $password - The password to save
	 *
	 */
	function create_session($login, $password){
		$this->CI->session->set_userdata(array('login'=>$login, 'pw'=>$password, 'logged'=>true));
	}
	
	/**
	 * Get ID - return the logged user id.
	 * 
	 * @return int
	 */
	function get_id(){
		return $this->user_data->id;
	}
	
	/**
	 * Get username - return the logged user username.
	 * 
	 * @return int
	 */
	function get_login(){
		return $this->CI->session->userdata('login');
	}
	
	/**
	 * Get name - return the logged user name.
	 * 
	 * @return string
	 */
	function get_name(){
		return $this->user_data->name;
	}
	
	/**
     * Match Password - returns true if the
     * argument is the same to the logged user
     *
     * @param string - the password to match
	 * @return boolean
	 */
    function match_password($password_string){
        if($this->session->userdata('logged') == 'true') {
    		return md5($entered_password) == $this->user_data->password;
        } else {
            return false;
        }
	}
	
	/**
     * Update Last Login - update the last login of the current user with the current date 
     *
     * @return boolean - the result of the operation
	 */
	function update_last_login(){
		$this->CI->db->where(array('id'=>$this->get_id()));
		return $this->CI->db->update('users', array('last_login' => date('Y-m-d')));
	}
	
	/**
     * Update Password - In the case you made a form for the user to change its
     * password, this function will change everything needed to maintain
     * the user logged in after updating the database
     *
	 * @param string $new_pw the new password
	 * @return boolean
	 */
	function update_pw($new_pw){
		$this->CI->session->set_userdata(array('pw'=>$new_pw));
		$this->user_data->password = $new_pw;
		return true;
	}
	
	/**
	 * Has Permission - returns true if the user has the received
	 * permission. Simply pass the name of the permission.
	 * 
	 * @param string $permission_name - The name of the permission
	 * @return boolean
	 */
	function has_permission($permission_name){
		if( ! $this->CI->session->userdata('logged')){
            return false;
        } else if (in_array($permission_name, $this->user_permission)) {
            return true;
        } else {
            return false;
        }
	}
	
	
	
	/**
	 * Update Login - update the login where it is needed.
	 * note: it wont update the database.
	 * 
	 * @param string $new_pw the new login
	 * @return boolean
	 */
	function update_login($new_login){
		$this->CI->session->set_userdata(array('login'=>$new_login));
		$this->user_data->login = $new_login;
		return true;
	}
	
	
	/**
	 * Destroy User - Destroy the user where its needed.
	 * 
	 * @return boolean
	 */
	function destroy_user(){
		$this->CI->session->set_userdata(array('login'=>"", 'pw'=>"", 'logged'=>false));
		$this->CI->session->sess_destroy();
		unset($this->user_data);
		return true;
	}


    /**
	 * Load Permission - Aux function to load the permissions
	 */
	function load_permission(){
		$permissions = $this->CI->db->join('users_permissions', 'users_permissions.permission_id = permissions.id')
									->get_where('permissions', array('users_permissions.user_id'=>$this->get_id()))
									->result();
		
		$user_permissions = array();
		
		foreach($permissions as $permission){
			$user_permissions[] = $permission->name;
		}
		$this->user_permission = $user_permissions;
    }

}
?>
