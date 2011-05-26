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


class CI_User_manager {
	
	protected $CI;

	function __construct(){
		$this->CI =& get_instance();
        
        // load session library
        $this->CI->load->library('session');
    }


    function save_user($full_name, $login, $password, $active = 1, $permissions=array()) {
        $hashed_password = md5($password);
        
        if( ! $this->login_exists($login)) {
            // This login is fine, proceed
            if ( $this->CI->db->insert('users', array('name'=>$full_name, 'login'=>$login, 'password'=>$hashed_password, 'active'=>$active )) ) {
                
                // Saved successfully
                $new_user_id = $this->CI->db->insert_id();

                // Add the permissions
                $this->add_permission($new_user_id, $permissions);

                // Return the new user id
                return $new_user_id;
            }
        } else {
            echo $this->CI->db->last_query();
            return false;
        }
    }

    function delete_user($user_id){
        return $this->CI->db->delete('users', array('id'=>$user_id));
    }

    function login_exists($login_name){
        $exists = $this->CI->db->get_where('users', array('login'=>$login_name))->row();
        return sizeof($exists) != 0;
    }

    function add_permission($user_id, $permissions) {
        if(is_array($permissions)) {
            if(sizeof($permissions) != 0) {
                return FALSE;
            }

            foreach($permissions as $permission) {
                $this->add_permission($user_id, $permission);
            }
        } else {
            return $this->CI->db->insert('users_permissions', array('user_id'=>$user_id, 'permission_id'=>$permissions));
        }
    }

    function get_users_with_permission($permission_name){
        $permission = $this->CI->db->get_where('permissions', array('name'=>$permission_name))->row();
        if(sizeof($permission) == 0) {
            return FALSE;
        } else {
            return $this->CI->db->get_where('users_permissions', array('permission_id'=>$permission->id))->result();
        }
    }
}
