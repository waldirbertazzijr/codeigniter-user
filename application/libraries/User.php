<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User Class
 *
 * @author		Waldir Bertazzi Junior
 * @link		http://waldir.org/
 */

/*
 * This constant is used to make the login method
 * dont update the last login on database
 * This is only for optmization pruposes.
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
	public $user_data = array();
	private $CI;

	/**
	 * Constructor
	 * 
     * Loads the session and crypt library.
     * Also gets a instance of CI class.
	 * 
	 */
	function __construct(){
		$this->CI =& get_instance();
		
		// checks if the database library is loaded
		if(!isset($this->CI->db)){
			show_error("Database library isn't loaded, please load it. It's recommended that you autoload it. Click <a href='http://codeigniter.com/user_guide/general/autoloader.html'>here</a> for more information about Codeigniter's autoloader.");
		}
        // load session and bcrypt library.
        $this->CI->load->library(array('session', 'bcrypt'));

        // autoload user if it exists
        $this->validate_session();
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
	 * Get Email - return the logged user email.
	 * 
	 * @return string
	 */
	function get_email(){
		return $this->user_data->email;
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
		return $this->user_data->name;
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
		if(!$this->validate_session()){
			$this->CI->session->set_flashdata('error_message', 'Invalid session.');
			redirect($destiny);
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
        if($this->validate_session()) {
	        // if its not logged we must clear the flashdata because it was filled on validate
            $this->CI->session->set_flashdata('error_message', '');
            redirect($destiny);
        }
	}
	
	/**
	 * Validate Session - Return true if the session stills valid
     * otherwise returns false. It also "generates" the user_data variable.
	 * 
	 * @return boolean
	 */
	function validate_session(){
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
			
			// chekcs if user is active or not
			if($user_query->active == 0) return false;

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
	            $this->_load_permission($this->user_data->id);

	            // create the user session
				$this->_create_session($login, $user_query->password);

				// updates last login if needed
				if($update_last_login){
	                $this->update_last_login();
	            }

				return true;
			} else {
				// invalid password
				return false;
			}
        } else {
            // Invalid credentials
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
	 * Destroy User - Destroy all the user session where is needed.
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
	 * Load Permission - Aux function to load the user permissions
	 * 
	 * @return array
	 */
	private function _load_permission(){
		$permissions = $this->CI->db->join('users_permissions', 'users_permissions.permission_id = permissions.id')
									->get_where('permissions', array('users_permissions.user_id'=>$this->get_id()))
									->result();
		
		$user_permissions = array();
		
		foreach($permissions as $permission){
			$user_permissions[] = $permission->name;
		}
		$this->user_permission = $user_permissions;
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
