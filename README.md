# Codeigniter User Library V. 1.3.1
This library is a *very simple* yet *powerful* user auth library for CodeIgniter, made for easy instalation and strong security. The library uses [Bcrypt](http://codahale.com/how-to-safely-store-a-password/) for hashing passwords to the database.
## Quick Start
This is a quick start guide to help you run codeigniter-user. This tutorial implies that you have basic notion of codeigniter mechanism like libraries and controllers.

* Import the _database schema.sql_ to your database.
* Copy the libraries to your _application/libraries_ folder. It includes [Bcrypt](https://github.com/waldirbertazzijr/codeigniter-bcrypt) and the codeigniter-user itself.
* Copy the language file under _language/english/codeigniter_user_lang.php_ to your own language folder. You also may wish to translate or change the strings there.
* Change your encryption key on your application _config.php_ file.
* Set up your database. This can be done on _database.php_, under config folder.
* If you want to see the demo login page, marge all the files (including the views and controllers) included.
* **If you installed the demo**, head to _index.php/login_ and try out your new user auth system.

## Usage
Here is listed some of the most common actions when managin the user auth flow on your site. Examples of:
### Logging a user in
You may put this in the "login" method of your website.

	// These variables may come from a form, for instance
	if($this->user->login($login, $password)){
		// user is now logged in, you may redirect it to the private page
		redirect('private_page');
	} else {
		redirect('login_page');
	}

### Validating a session (user is logged in)
You can create custom actions with this function.

	if($this->user->validate_session()) {
		echo "If you can see this you are logged in.";
	}

### Auto redirect on invalid session
Auto redirects if the user isn't logged in. The first parameter tells where to redirect if theres a invalid session (controller/method/etc). If you wish to lock the whole controller, you can put it on the constructor.

	$this->user->on_invalid_session('home/login');

### Auto redirect on valid session
Auto redirect function if the user is logged in. The first parameter tell where to redirect if theres a valid session (controller/method). Ideal for login pages.

	$this->user->on_valid_session('home');

### Showing errors
Codeigniter-user library uses two flashdata names for displaying errors.

### Get the current logged in name, id & email
Simple way to retrieve the logged user name and login.

	echo 'Hello, ' . $this->user->get_name() . '! Your ID is: ' . $this->user->get_id() . ' and your e-mail is: ' . $this->user->get_email();


### Get the current logged in data
Simple way to retrieve the logged user data. All the available data is dumped into this variable.

	var_dump($this->user->user_data);


### Check permission
Checks if user has a permission. The first parameter is the permission name.

	if($this->user->has_permission('editor')){
		$this->load->view('editor_menu');
	}


### Logout user
Removes all session from browser and redirects the user to the received path.

	$this->user->destroy_user('home/login');

### Change user password or login on the fly
**After updating the database**, both login and password, this functions must be called right after for updating user session.

	// changing the user login and password with received data from form
	$this->user->update_pw($this->input->post('new_password'));
	$this->user->update_login($this->input->post('new_login'););


## Managing users
There is a separated library for user managing. After setting up the database config, load up the user_manager library. There are some examples of:

### Adding a new user
	$fullname = "Johnny Winter";
	$login = "john"
	$password = "123becarefulwithafool";
	$active = true;
	$permissions = array(1, 3);
	$new_user_id = $this->user_manager->save_user($fullname, $login, $password, $active, $permissions);


### Creating a permission
	$permission_id = $this->user_manager->save_permission('editor', 'The editors of my website.');

### Deleting a user
	if( $this->user_manager->delete_user($user_id)){
		echo "User was deleted.";
	}

---
# Changelog
* Version 1.3.1
	* Added the language file outside the code.
	* Logout method now receives the destiny controller for auto redirect.
* Version 1.3
	* Upgraded hash function to Bcrypt. Passwords are much safer and stronger now.
	* Strong optimization on cookie password storage and hash comparison
	* No more rehashing after database query.
* Version 1.1
	* Fixed some broken functions.
	* Updated doc with brief description of methods.
* Version 1.0
	* Added sha1 support.
	* Added password salting support.

# Roadmap
* Version 1.4
	* Custom database fields and names for more flexibility.
* Version 1.5
	* "Remember-me" feature.
* Version 1.6
	* Custom fields attached to each user, so you can choose where to put the info.