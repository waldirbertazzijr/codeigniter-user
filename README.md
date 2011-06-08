# Codeigniter User Library V. 0.8
This library is a **very simple** user auth library for Codeigniter. If you're looking into more complex and secure, this is not for you.
The library works by simple passwords md5 hashing with some functions that can help you out.
I'll adding things to it as I need em. Fell free to request a pull.

## Quick Start
* Import the _userschema.sql_ to your database.
* Merge the content of the folders with you Codeigniter root.
* You may need to set your secret session key on your config file and also set up your database config properly if you haven't yet.
* Head to /index.php/login/ and try your new auth system.

## Usage
Here is listed some of the most common actions when managin the user auth flow on your site. Examples of:
### Logging a user in
	if($this->user->login($login, $password)){
		echo "Logged in!";
	} else {
		echo "Wrong credentials!";
	}

### Validating a session
	if($this->user->validate_session()) {
		echo "Session is still valid.";
	}

### Redirect user based on valid session
	$this->user->on_invalid_session('home/login');

### Get the current logged in user id
	echo $this->user->get_id();

### Get current user name
	Welcome <?php echo $this->user->get_name();?>!

### Check permission
	if($this->user->has_permission('editor')){
		$this->load->view('editor_menu');
	}

### Logout user
	$this->user->destroy_user();


## Managing users
There is a separated library for user managing. After setting up the database config, load up the user_manager library. Some examples of
### Creating a permission
	$permission_id = $this->user_manager->save_permission('editor', 'The editors of my website.');

### Adding a new user

$fullname = "Michael Jackson";
	$login = "MJ"
	$password = "beat_it";
	$active = true;
	$permissions = array(1, 3, 6);
	$this->user_manager->save_user($fullname, $login, $password, $active, $permissions);


### Deleting a user
	$this->user_manager->delete_user($user_id);

## Documentation
I still working on a good, standalone documentation for the project, it will be released soon. Thanks.

## Codeigniter Sparks
[Codeigniter Sparks](http://getsparks.org/) is an amazing project. It's something like a "package manager" for Codeigniter. Im on that, when I got some free time I'll read how can I put my package on Sparks.
