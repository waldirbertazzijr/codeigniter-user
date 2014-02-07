<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* User Class
*
* @package		Orion
* @subpackage	Libraries
* @category		Users
* @author		Waldir Bertazzi Junior
* @link 		http://waldir.org/
*/

class User_manager {
	
	private $CI;

	function __construct(){
		$this->CI =& get_instance();
        
		// checks if the database library is loaded
		if(!isset($this->CI->db)){
			show_error("You need the database library to use the User Library. Please check your configuration.");
		}

		// load session and bcrypt library.
		$this->CI->load->library(array('session', 'bcrypt'));
	}

	/**
	* Creates a new user and return its ID
	*
	* @return int ID
	* @param String full user name
	* @param String full user password
	* @param Bool is user active?
	* @param Array array of int permissions
	* @author Waldir Bertazzi Junior
	**/
	function save_user($full_name, $login, $password, $email, $active = 1, $permissions=array()) {        
		
		// first we must check if is a valid insert
		if( ! $this->login_exists($login) && $full_name!= "") {
	
			// generate the hashed password
			$hashed_password = $this->CI->bcrypt->hash($password);
	
			// This login is fine, proceed
			if ( $this->CI->db->insert('users', array('name'=>$full_name, 'login'=>$login, 'email'=>$email, 'password'=>$hashed_password, 'active'=>$active )) ) {
                
				// Saved successfully
				$new_user_id = $this->CI->db->insert_id();

				// Add the permissions
				$this->add_permission($new_user_id, $permissions);

				// Return the new user id
				return $new_user_id;
			}
		} else {
			// Login already exists or full name is empty
			return false;
		}
	}
    
	// Delete the user
	function delete_user($user_id){
		return $this->CI->db->delete('users', array('id'=>$user_id));
	}

	// Check if there is already a login with that name
	function login_exists($login_name){
		$exists = $this->CI->db->get_where('users', array('login'=>$login_name))->row();
		return sizeof($exists) != 0;
	}

	// Checks if user already has the permission on database
	function user_has_permission($user_id, $permission_id){
		$result = $this->CI->db->get_where('users_permissions', array('user_id' => $user_id, 'permission_id' =>$permission_id));
		return ( $result->num_rows() == 1 );
	}

	// Links a permission with a user
	function add_permission($user_id, $permissions) {
		// If array received we must call this recursively
		if(is_array($permissions)) {
			if(sizeof($permissions) == 0) {
				return FALSE;
			}
			// Foreach permission in the array call this function recursively
			foreach($permissions as $permission) {
				$this->add_permission($user_id, $permission);
			}
		} else {
			// Check if user already has this permission
			if( ! $this->user_has_permission($user_id, $permissions) ) {
				return $this->CI->db->insert('users_permissions', array('user_id'=>$user_id, 'permission_id'=>$permissions));
			} else {
				// User already has this permission
				return TRUE;
			}
		}
	}

	// Creates a new permission
	function save_permission($permission_name, $permission_description){
		$exists = $this->CI->db->get_where('permissions', array('name'=>$permission_name));
		if( $exists->num_rows() >= 1 ) {
			return $exists->row()->id;
		} else { 
			$insert = $this->CI->db->insert('permissions', array('name'=>$permission_name, 'description'=>$permission_description));
			if($insert) {
				return $this->CI->db->insert_id();
			} else {
				return FALSE;
			}
		}
	}

	// Gets all users with a selected permission
	function get_users_with_permission($permission_name){
		$permission = $this->CI->db->get_where('permissions', array('name'=>$permission_name))->row();
		if(sizeof($permission) == 0) {
			return FALSE;
		} else {
			return $this->CI->db->get_where('users_permissions', array('permission_id'=>$permission->id))->result();
		}
	}

	// Add (and saves to database) a custom user information
	function set_custom_field($user_id, $name, $value){
		$field = $this->CI->db->get_where('users_meta', array('user_id'=>$user_id, 'name'=>$name));
		if($field->num_rows() == 0){
			return $this->db->insert('users_meta', array('user_id'=>$user_id, 'name'=>$name, 'value'=>$value));
		} else {
			return $this->db->update('users_meta', array('user_id'=>$user_id, 'name'=>$name, 'value'=>$value), array('user_id'=>$user_id));
		}
	}
	
	// Add (and saves to database) a custom user information
	function get_custom_field($user_id, $name, $value){
		$field = $this->CI->db->get_where('users_meta', array('user_id'=>$user_id, 'name'=>$name));
		if($field->num_rows() == 0){
			return FALSE;
		} else {
			return $field->row()->value;
		}
	}

}
