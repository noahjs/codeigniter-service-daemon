codeigniter-service-daemon
==========================

Used for long running CLI daemons using Codeingiter

Cli.php
=======
This file is used as the command line entrance into the codeigniter framework.
Any controller method you want to access from commandline needs to be written here.

Brains
======
This is the, pardon pun, brains of the operation. Use this file as similar to service calls you are familiar with.
Have a billing daemon running and want to check status
./brains billing status
or restart or stop etc..

./core/daemon_controller.php
=======
All Daemon controllers extend this class

./controllers/cli_tools.php
========
An example of some tools we built for easier command line usage. Allowed me to search DB for a domain and find the file path.
Also a where function that was used to validate I was on the correct box before running any comands.

./controllers/daemon_cron.php
========
A daemon example that acts like a cron.


./controllers/daemon_db.php
========
A daemon example that reacts based of information stored in a DB like a normal worker process.