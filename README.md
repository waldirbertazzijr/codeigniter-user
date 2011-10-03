# Codeigniter User Library V. 1.0
This library is a *very simple* user auth library for Codeigniter. The libraries uses sha1 hashing with salt.
I'll adding things to it as I need em. Fell free to request a pull.

## Quick Start
This is a quick guide to help you run your user system.

* Import the _database schema.sql_ to your database. There are 3 tables with users and permissions.
* Merge the content of the folders with you Codeigniter root.
* Change your encryption key on your application _config.php_ file and also set up your database config properly if you haven't yet.
* Load your database library automatically. This can be done on _autoload.php_, under _libraries_ session.
* Head to http://example.com/index.php/user and try your new auth system. There is a simple demonstration within this library.

## Usage
Here is listed some of the most common actions when managin the user auth flow on your site. Examples of:
### Logging a user in
	if($this->user->login($login, $password)){
		redirect('private_page');
	} else {
		echo "Wrong credentials!";
	}

### Validating a session
	if($this->user->validate_session()) {
		echo "This is secret.";
	}

### Auto redirect on invalid session
	$this->user->on_invalid_session('home/login');

### Auto redirect on valid session
	$this->user->on_valid_session('home/login');

### Get the current logged in name/id
	echo $this->user->get_name();
	echo Welcome, $this->user->get_id();

### Check permission
	if($this->user->has_permission('editor')){
		$this->load->view('editor_menu');
	}

### Logout user
	$this->user->destroy_user();


## Managing users
There is a separated library for user managing. After setting up the database config, load up the user_manager library. Some examples of

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

## Codeigniter Sparks
[Codeigniter Sparks](http://getsparks.org/) is an amazing project. It's something like a "package manager" for Codeigniter. Im on that, when I got some free time I'll read how can I put my package on Sparks.
