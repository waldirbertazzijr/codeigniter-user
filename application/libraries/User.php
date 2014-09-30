<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Codeigniter User Class
*
* @author		Waldir Bertazzi Junior
* @link 		http://waldir.org/
*/

/*
* This constant is used to make the login 
* keep the last login on the database.
*/
define('DONT_UPDATE_LOGIN', false);

define('PASSWORD_IS_HASHED', true);
define('PASSWORD_IS_NOT_HASHED', false);


class User {
	
	/**
	* User Data - This variable holds all user data after session validation
	*
	* @var array
	*/
	public $user_data			= array();
	public $custom_data			= array();
	public $user_permission		= array();
	private $use_custom_fields	= true;
	private $CI;

	/**
	* Constructor
	* 
	* Loads the session and crypt library.
	* Also gets a instance of CI class.
	*/
	function __construct(){
		$this->CI =& get_instance();
		
		// checks if the database library is loaded
		if(!isset($this->CI->db)){
			show_error("Database library isn't loaded, please load it. It's recommended that you autoload it. Click <a href='http://codeigniter.com/user_guide/general/autoloader.html'>here</a> for more information about Codeigniter's autoloader.");
		}
		// load session and bcrypt library.
		$this->CI->load->library(array('session', 'bcrypt'));
		
		// Loads the language file for the library (more translations welcome)
		$this->CI->lang->load('codeigniter_user', 'english');

		// autoloads the user
		$this->validate_session();
	}
	
	/**
	* Get ID - return the logged user id.
	* 
	* @return int
	*/
	function get_id(){
		if (isset($this->user_data->id))
			return $this->user_data->id;
		else
			return false;
	}
	
	/**
	* Get Email - return the logged user email.
	* 
	* @return string
	*/
	function get_email(){
		if (isset($this->user_data->email))
			return $this->user_data->email;
		else
			return false;
	}
	
	/**
	* Get username - return the logged user username.
	* 
	* @return string
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
		if (isset($this->user_data->name))
			return $this->user_data->name;
		else
			return false;
	}
	
	
	/**
	* 
	* On Invalid Session - Simple redirect if the user is not
	* already logged in. Make it easy to create login only pages.
	* 
	* @param string $destiny - the destiny to the user is not logged in
	* 
	*/
	function on_invalid_session($destiny){
		if(!is_object($this->user_data) && !$this->validate_session()){
			$this->CI->session->set_flashdata('error_message', $this->CI->lang->line('error_invalid_session'));
			redirect($destiny, 'refresh');
		}
	}
	
	/**
	* On Valid Session - Simple redirect the user
	* if its already logged in. Make it easy to create login pages.
	* 
	* @param string $destiny - the destiny to the user is logged in
	*
	*/
	function on_valid_session($destiny){
		if(is_object($this->user_data) && $this->validate_session()) {
			// if its not logged we must clear the flashdata because it was filled with 
			// error message on validate
			redirect($destiny, 'refresh');
		}
	}
	
	/**
	* Validate Session - Return true if the session stills valid
	* otherwise returns false. It also "generates" the user_data variable.
	* 
	* @return boolean
	*/
	function validate_session(){
		if (!$this->CI->session->userdata('logged') && !is_object($this->user_data)) {
			return false;
		}
		// This function doesnt need to update the last_login on database.
		if($this->login($this->CI->session->userdata('login'), $this->CI->session->userdata('pw'), DONT_UPDATE_LOGIN, PASSWORD_IS_HASHED)){
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
	* @param bool $update_last_login - set if this login will update the last login field or not
	* @param bool $hashed_password - notifies the function that the received password is already hashed.
	*/
	function login($login, $password, $update_last_login = true, $hashed_password = false){
		$user_query = $this->CI->db->get_where('users', array('login'=>$login));
		
		if($user_query->num_rows()==1){
			// get user from the database
			$user_query = $user_query->row();
			
			// checks if user is active or not
			if($user_query->active == 0) {
				$this->CI->session->set_flashdata('error_message', $this->CI->lang->line('error_inactive_account'));
				return false;
			}

			// validates hash
			$valid_password = false;
			
			// if password is not hashed
			if($hashed_password == PASSWORD_IS_NOT_HASHED){
				// validate rehashing the password
				$valid_password = $valid_password || $this->CI->bcrypt->compare($password, $user_query->password);
			} else {
				// password already hashed
				$valid_password = $valid_password || ($user_query->password == $password);
			}
			
			if($valid_password){
				// save the user data
				$this->user_data = $user_query;

				//loads the user permissions
				$this->_load_permission();
				
				//loads the user custom fields
				$this->_load_custom_fields();

				// create the user session
				$this->_create_session($login, $user_query->password);

				// updates last login if needed
				if($update_last_login){
					$this->update_last_login();
				}

				return true;
			} else {
				// invalid password
				$this->CI->session->set_flashdata('error_message', $this->CI->lang->line('error_invalid_password'));
				return false;
			}
		} else {
			// Invalid login
			$this->CI->session->set_flashdata('error_message', $this->CI->lang->line('error_invalid_login'));
			return false;
		}
	}
	
		
	/**
	* Match Password - returns true if the
	* argument is the same to the logged user
	*
	* @param string - the password to match
	* @return boolean
	*/
	function match_password($password_string){
		return $this->CI->bcrypt->compare($password_string, $this->user_data->password);
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
	* note: it also updates the database
	* 
	* @param string $new_pw the new login
	* @return boolean
	*/
	function update_login($new_login){
		// updates the session
		$this->CI->session->set_userdata(array('login'=>$new_login));
		$this->user_data->login = $new_login;
		
		// update the database
		$sts = $this->CI->db->update('users', array('login'=>$new_login), array('id'=>$this->get_id()));
		
		return $sts;
	}
	
	/**
	* Update Password - In the case you made a form for the user to change its
	* password, this function will change everything needed to maintain
	* the user logged in.
	*
	* @param string $new_pw the new password
	* @return boolean
	*/
	function update_pw($new_pw){
		// hashes the password
		$new_pw = $this->CI->bcrypt->hash($new_pw);
		
		// updates the session
		$this->CI->session->set_userdata(array('pw'=>$new_pw));
		$this->user_data->password = $new_pw;
		
		// update the database
		$sts = $this->CI->db->update('users', array('password'=>$new_pw), array('id'=>$this->get_id()));
		
		return $sts;
	}
	
	
	/**
	* Destroy User - Destroy all the user session where is needed.
	* 
	* @return boolean
	*/
	function destroy_user($destiny){
		// remove everything from the session
		$this->CI->session->set_userdata(array('login'=>"", 'pw'=>"", 'logged'=>false));
		$this->CI->session->sess_destroy();
		
		// just in case...
		unset($this->user_data);

		// CI 2.1.3 bug: after destroy session, can't set flashdata because session_id
		$this->CI->session->sess_create();
		
		// adds the logout message
		$this->CI->session->set_flashdata('success_message', $this->CI->lang->line('success_logout'));
		
		// redirects the user to the destiny
		redirect($destiny, 'refresh');
	}
	
	
	/**
	 * Add or modify (and save to database) a custom user information
	 *
	 * @return bool
	 * @author Waldir Bertazzi Junior
	 **/
	function set_custom_field($user_id, $name, $value){
		if (!$this->use_custom_fields) return false;
		
		$field = $this->CI->db->get_where('users_meta', array('user_id'=>$user_id, 'name'=>$name));
		if($field->num_rows() == 0){
			return $this->CI->db->insert('users_meta', array('user_id'=>$user_id, 'name'=>$name, 'value'=>$value));
		} else {
			return $this->CI->db->update('users_meta', array('user_id'=>$user_id, 'name'=>$name, 'value'=>$value), array('name'=>$name, 'user_id'=>$user_id));
		}
	}
	
	/**
	 * Retrieve a custom user information
	 *
	 * @return string
	 * @author Waldir Bertazzi Junior
	 **/
	function get_custom_field($name){
		if (!$this->use_custom_fields) return false;
		
		if (isset($this->custom_data[$name]))
			return $this->custom_data[$name];
		else
			return false;
	}


	/**
	* Load Permission - Aux function to load the user permissions
	* 
	* @return array
	*/
	private function _load_permission()
	{
		$permissions = $this->CI->db
		->join('users_permissions', 'users_permissions.permission_id = permissions.id')
		->get_where('permissions', array('users_permissions.user_id'=>$this->get_id()))
		->result();
		
		$user_permissions = array();
		
		foreach($permissions as $permission) $user_permissions[] = $permission->name;

		$this->user_permission = $user_permissions;
	}
	
	/**
	 * Load Custom Fields - Aux function to load the user custom fields
	 *
	 * @return void
	 * @author Waldir Bertazzi Junior
	 **/
	private function _load_custom_fields()
	{
		if (!$this->use_custom_fields) return false;
		
		$fields = $this->CI->db
		->select('name, value')
		->get_where('users_meta', array('users_meta.user_id'=>$this->get_id()))
		->result();
		
		$custom_data = array();
		
		foreach ($fields as $field) $custom_data[$field->name] = $field->value;
		
		$this->custom_data = $custom_data;
	}
	
	/**
	* Create session - creates the session with valid data
	* its used by the validate function.
	* 
	* @param string $login - The login to save
	* @param string $password - The password to save
	*
	*/
	private function _create_session($login, $password){
		$this->CI->session->set_userdata(array('login'=>$login, 'pw'=>$password, 'logged'=>true));
	}
}
?>