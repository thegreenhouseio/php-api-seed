# php-api-seed
PHP7 / Slim Seed Project for API projects in The Greenhouse.  This repository is meant to provide a starting point
for a backend RESTful API as a companion to a static frontend SPA.  Generally  for your application, the overview would
be something about your project though.

## Stack
As implied by the name, this project is written in [PHP >= 7.1][]  and functions within the [AMP][] stack.  The following 
are the core tools used for development and deployment

- [Phing](https://www.phing.info/) - Phing is a build tool for PHP projects.  It is expected that the `phing` binary is 
installed globally in dev / prod environments. 
- [Composer](https://getcomposer.org/) - PHP package manager.
- [Slim v2](https://www.slimframework.com/) - API centric micro-framework.
- [Phar](http://php.net/manual/en/book.phar.php) - Build artifact of the project (PHP Archive).
- [Vagrant v1.9.5](https://www.vagrantup.com/) - used with VirtualBox to provide a headless VM for local development
- [VirtualBox v5.1.22](https://www.virtualbox.org/wiki/VirtualBox) - Virtualization software for managing a Ubuntu based VM 
local development environment
- [MySQL v5.5](https://www.mysql.com/) - Relational database.
- [Apache 2](https://httpd.apache.org/) - recommended HTTP webserver, and used locally for development.
- [Ubuntu 16.04](https://www.ubuntu.com/) - Recommended Linux distrubution, and used locally for development

[PHP >= 7.1]: http://php.net
[AMP]: https://en.wikipedia.org/wiki/List_of_Apache%E2%80%93MySQL%E2%80%93PHP_packages
[wiki]: https://github.com/thegreenhouseio/php-api-seed/wiki


Please familiarize yourself with the project's [wiki][] for more supplemental information on configuring this project to 
run in CI and production environments.


## Project Layout

- _ini/_ - tracked and un-tracked environment based configuration files
- _bin/_ - executable scripts for Jenkins, Vagrant, etc
- _sql/_ - sql backups
- _src/_ - application code
- _src/resources/_ - available collections to map to endpoints
- _src/routes/_ - map of endpoints to resources
- _src/services/_ - helper / utitlity functions, classes not mapped to collectionsgit
- _tests/_ - unit and integration tests organized to match the _src_ direectory


## Development
This section covers information relevant to local development for the project.

### Vagrant
This project uses Vagrant for local development and so all instructions have that assumption in place.  Please make sure 
you have installed the recommended versions of Vagrant and Virtual Box.  These steps are for getting into a Vagrant VM
development environment on your local machine:

```bash
# start vagrant and ssh into it
$ vagrant up
$ vagrant ssh

# move into the mapped drive that points the root of the repo
$ cd /vagrant

# notice all our files
vagrant@thegreenhouse:/vagrant$ ls -l
total 168
drwxr-xr-x 1 vagrant vagrant    170 Jun  4 17:55 bin
drwxr-xr-x 1 vagrant vagrant    102 Jun  4 14:26 build
-rw-r--r-- 1 vagrant vagrant   5827 Jun  4 14:28 build.xml
-rwxr-xr-x 1 vagrant vagrant    352 Jun  4 14:19 composer.json
-rw-r--r-- 1 vagrant vagrant 144338 Jun  3 02:22 composer.lock
drwxr-xr-x 1 vagrant vagrant    204 Jun  4 13:46 ini
-rw-r--r-- 1 vagrant vagrant   5296 Jun  4 18:06 README.md
drwxr-xr-x 1 vagrant vagrant    170 Jun  4 14:26 reports
drwxr-xr-x 1 vagrant vagrant    102 Jun  4 14:14 sql
drwxr-xr-x 1 vagrant vagrant    306 Jun  4 14:25 src
drwxr-xr-x 1 vagrant vagrant    136 Jun  3 02:17 test
-rw-r--r-- 1 vagrant vagrant    429 Jun  3 02:17 Vagrantfile
drwxr-xr-x 1 vagrant vagrant   1088 Jun  4 14:22 vendor

# to exit the shell, run exit
vagrant@thegreenhouse:/vagrant$ exit

# to tear down, run destroy back on your local machine
$ vagrant destroy
```

**MySQL / Apache**
This project's local development environment is equipped with a full AMP stack for development, and so an Apache 
webserver and MySQL database are configured and available and runnable from the command line (turned on by default in
Vagrant).  

In addition, the Vagrant provisioning script populates MySQL from a `.sql` file so a fresh database is setup each time
`vagrant up` is run.  In this way, it is easy to develop and test against production data, by getting a simple SQL
dump from the production database and saving it to this project.

**Notes**
- For more on all available Vagrant commands, see the [manual](https://www.vagrantup.com/docs/cli/).
- For OSX users, there is a GUI application called [Vagrant Manager](http://vagrantmanager.com/).

### Tasks
An overview of Phing commands that can be run for this project

#### Development
For the most part, you will just want to write code, write some tests, and the it passes or fails.  This 
can be done with

```
$ phing develop
```

#### Production

1. Standard production build (WITH linting, docs, tests)

```
$ phing build
```

2. "Expedited" production build (NO linting, docs, tests, just packaging, say for local development)

```
$ phing build:exp
```

You can test from the Vagrant VM using cURL
`curl localhost/api/albums`

Or the browser / POSTman against your host machine
`localhost:4567/api/albums`


**Note:** When wanting to teste the API via cURL or POSTman add this to the command you run
```
-D buildDir=/home/vagrant/build && cp src/.htaccess /home/vagrant/build/
```

### Testing
PHPunit is used for unit testing
`phing test`

To see code coverage, open _{path/to/repo/in/your/filesystem}/reports/coverage_result/index.html_ in your browser

### API Documentation
To generate API documentation run
`$ phing clean`
`$ phing docs`

and open _{path/to/repo/in/your/filesystem}/reports/docs/index.html_ in your browser


### Dependency Management
Composer is used for managing / install 3rd party dependencies for the project.  It also creates an autoloader.

To install all the dependencies from _composer.json_ (Vagrant will do this for you)
`$ composer install`

To install a new dependency
`$ composer require {package-name}`

To upgrade an existing dependency
`$ composer require {package-name}`


### Creating a new resource / endpoint (/albums, /artists, etc )
1. Copy paste an existing resource (like Artists)
2. Update $name, $tableName, $requiredParams, $updateParams, $optionalParams  
3. Update method params (getFoo, getFooById, etc)
4. Add new case in src/resources/ResfulResourceBuilder
5. Copy paste an existing test (like Artists in tests/resources) and update for all CRUD operations
6. Test each CRUD operation one at a time using `phing test`
7. Add resource name to $resources array
8. Add a "route" case in controller.php
9. Create a route file in /routes, to match your resource name and route case
10. Test all endpoints in POSTman

## Release Management
// TODO