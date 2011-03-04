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
class CI_User {

	public $user_permissions 		= array();
	public $user_data 				= array();
	private $current_session 		= 0;
	protected $CI;

	/**
	 * Constructor
	 * 
	 * Loads the session library and check if user isnt already
	 * logged in.
	 * 
	 */
	function __construct(){
		// load session library
		$this->CI =& get_instance();
		$this->CI->load->library('session');
		
		// check if already logged
		if($this->CI->session->userdata('logged'))
			$this->validate_session();
	}
	
	/**
	 * 
	 * On Invalid Session - Simple redirect if the user isnt
	 * already logged in. This is very useful if you use on
	 * Controllers constructor.
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
		if($this->validate_session())
			redirect($destiny);
		// if its not logged we must clear the flashdata because it was filled on validate
		// with invalid data maybe
		$this->CI->session->set_flashdata('error_message', '');
	}
	
	/**
	 * Validate Session - Return true if the session stills valid
	 * 
	 * @return boolean
	 */
	function validate_session(){
		if($this->login($this->CI->session->userdata('login'), $this->CI->session->userdata('pw'))){
			return true;
		}
		$this->CI->session->set_flashdata("message", "Invalid session.");
		return false;
		
	}
	
	/**
	 * Login - Receives the user and the password, verifies it
	 * and create a new session.
	 * 
	 * @param string $login - The login to validade
	 * @param string $password - The password to validate
	 */
	function login($login, $password){
		$user_query = $this->CI->db->get_where('users', array('login'=>$login, 'password'=>md5($password)));
		if($user_query->num_rows()==1){
			// save the user data
			$this->user_data = $user_query->row();
			$this->load_permission($this->user_data->id);									
			$this->create_session($login, $password);
			// updates last login
			$this->update_last_login();
			return true;
		} else {
			$this->CI->session->set_flashdata('error_message', 'Login or password invalid.');
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
	 * Get name - return the logged user name.
	 * 
	 * @return string
	 */
	function get_name(){
		return $this->user_data->name;
	}

	/**
	 * Match Password - check if the received password
	 * is the same as the current logged user.
	 * 
	 * @return boolean
	 */
	function match_password($entered_password){
		return md5($entered_password) == $this->user_data->password;
	}
	
	/**
	 * Update Last Login - update the database with today date.
	 * 
	 */
	function update_last_login(){
		$this->CI->db->where(array('id'=>$this->get_id()));
		$this->CI->db->update('users', array('last_login' => date('Y-m-d')));
	}
	
	/**
	 * Has Permission - returns true if the user has the received
	 * permission. Simply pass the name of the permission.
	 * 
	 * @param string $permission_name - The name of the permission
	 * @return boolean
	 */
	function has_permission($permission_name){
		if( ! $this->CI->session->userdata('logged'))
			return false;
		if (in_array($permission_name, $this->user_permission))
			return true;
		return false;
	}
	
	/**
	 * Update Password - update the password where it is needed.
	 * note: it wont update the database.
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
