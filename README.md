# Codeigniter User Library V. 0.6 
This library is a **very simple** user auth library for Codeigniter. If you're looking into more complex and secure, this is not for you.
The library works by simple passwords md5 hashing with some functions that can help you out.
I'll adding things to it as I need em. Fell free to request a pull.

## Quick Start
* Import the _userschema.sql_ to your database.
* Merge the content of this rep with you Codeigniter root.
* You may need to set your secret session key on your config file.
* Head to http://example.com/index.php/login/ and try it.

## Adding a user
For now, you have to query the database and insert a new user on users table. The links between users and permissions are done on the link table manually for now.

## Documentation
I still working on a good documentation for the project, it will be released soon. Thanks.

## Codeigniter Sparks
[Codeigniter Sparks](http://getsparks.org/) is an amazing project. It's something like a "package manager" for Codeigniter. Im on that, when I got some free time I'll read how can I put my package on Sparks.
