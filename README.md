# PHP Project Creator

PHP Project Creator is a CLI tool being executed in a ready-to-use docker container to create customized docker-compose setups
all around web development in the PHP universe. Simply download this software, start the project docker container, connect to the running
container over SSH and run one simple command to create your development stack.

You can also edit the existing service configurations or add your own. Doing so, you can focus only on the specific services you want to add or manipulate,
without thinking too much about compatibility or the details of how the container connects to the other containers. If you need to install other
libraries on your container and/or PHP extensions, you can use a set of generic package names instead of the exact name used in the container's 
operating system. It is also quite simple to add more package names to the library in case you need one which is not yet described.

## License

This project is released under the MIT License, see [LICENSE](LICENSE.md) file for license rights and limitations.


# For Users

## Get started

### Prerequisites

To be able to work with the PHP Project Creator, you need to have a functioning docker environment running on your system.
[Download](https://www.docker.com/get-started/) your version of Docker and follow the [documentation](https://docs.docker.com/get-docker/)
if you are not familiar with Docker already. In some cases you have to manually install [Docker Composse](https://docs.docker.com/compose/install/) after.

### Start the Container

Nothing else is needed, as soon as Docker is running you can start the container. Navigate to the project creator folder and
execute 
```
docker-compose up -d
```
When the container is built and running, open a terminal on it using
```
docker exec -ti <container name> /bin/bash
```
or any kind of Docker integration in your IDE (the way I prefer this).
Now you are ready to use the PHP Project Creator.

### Usage

The whole tool is a single command application, so everything you want to do with it starts like this:
```
php php-project-creator
```
The command supports one argument with two basic options:
#### List
```
php php-project-creator list
```
The "list" argument simply outputs all currently supported container configurations which you can choose from to create your custom setup.
There are currently no other options for the list command.
#### Create
```
php php-project-creator create
```
The "create" argument is the main command for the tool which creates the Docker Compose stacks.
Use the three options "--php", "--webserver" and "--database" and set them to a valid value from the output of the "list"
command to configure your custom Docker Compose stack. The tool will automatically check if your services meet some compatibility
and dependency requirements.

Here are two basic usage examples, which should give you a good idea of how to use the tool:
```
php php-project-creator create --webserver=apache --database=mysql
```
to create a classic LAMP stack setup with PHP/Apache and MySQL.
```
php php-project-creator create --php=fpm-gd --webserver=nginx --database=postgres
```
to create a custom 3-component stack with an Nginx as webserver on one container, PHP as FPM with preinstalled GD Image library on a second
and a Postgres SQL database on the last container.


# For developers
First of all, thank you for your interest in helping out the development of this early release tool.
Currently the tool is more in a prototype state, it can already be used but the number of available configurations is quite limited
and there are more improvements planned on the dependency checks. I am happy about everybody who wants to contribute to this project.

## Basic architecture
The tool is realised as a [Symfony Single Command Application](https://symfony.com/doc/current/components/console/single_command_tool.html) but
does not use the Symfony framework itself. Besides that, the command uses 5 implemented services which serve
different purposes. There are a DependencyManager to handle the dependency checks, a ConfigFileManager to
copy or arrange internal configuration files for the containers and provide the list command, a requirement manager to resolve
the information about where to install which packages or PHP extensions, a YAML reader as a wrapper for the PHP YAML functions,
and finally a DockerComposeGenerator, which takes an array of configurations and turns them into a valid docker-compose.yml file.

In addition to that there are the configuration snippets for the single services in the config folder.
The .yml snippet files can be found in their respective folder

## Main execution line
In a typical use case (like our first example in the Users section), the create command first builds up a directory path
which is always in a pattern like "&lt;php>-&lt;webserver>-&lt;database>" with every non-filename character being transformed into "-".
Then the tool reads the configuration for the selected services from the config folder. After that the tool checks
the dependencies between the configurations. Right now it is basically evaluating whether a version string in a meta configuration called "service-version"
matches the requirements set by the configuration of the dependent service.
If the depencendy check was successful the tool moves on to copy internal configuration files for the container services and creates their
entrypoints. The data gets prepared by the DockerComposeGenerator, which includes the RequirementsManager to rearrange the configuration array.
Finally, the configuration array gets rendered into a YAML file at the location of the created folder.

## Adding new configurations
Before we actually show how to add new or edit existing container configurations, lets first talk a bit about how those configurations in the "config" folder are handled.
The configuration files for the internal services are explicitly excluded here, if you want to learn about e.g. how to configure Nginx, please
go to their respective manual page. They are also not very important for
First, the whole array is read from the three separate config files for PHP, Webserver and Database. We have two different settings types: Actual container configurations which later appear
one to one in the docker-compose.yaml, and meta configurations which are set to help the tool putting the separate services together in a compatible way,
as well as supporting the dependency checks and defining the requirements to install additional libraries.

To distinguish between real service configurations and additional settings, there is only one array (DockerComposeGenerator.php, line 31) which defines configuration keys which should be excluded
while building a docker-compose.yml from the final array. Whenever you need to add a new meta configuration key, you have to add the key here, or it will be added
to the final docker-compose.yaml, thus making it invalid. This also means you can easily add missing Docker configurations by simply putting the same key/value pair in the
seperate config files.

### Meta configurations currently in use:

- **dependencies**: For dependency checks. Add any service under its relative category here to set it as dependency for the current container (see the apache.yaml example configuration)
- **service-version**: Contains a simple version string, again used for dependency checks. Although it is not strictly required, it is highly recommended adding this config to each service definition.
- **is-php-installed**: A flag for webserver configuration if the already include PHP, so the PHP requirements must instead be installed on the webserver container.
- **os_type**: Distribution of the operating system in the container. Right now, only "debian" and "alpine" are supported. This flag is only neccessary if the system is NOT "debian" and if additional libraries are to be installed
- **requires**: Tells other services, which libraries they need to install in order for the current container to work properly. 
- **finalCommands**: These are the commands to start the main process of the services. Usually they are executed automatically, but if you install libraries, the main process can be terminated and needs to be restarted after installation. This configuration tells the container how to do so.

## Future plans

First of all we plan to improve the creation of the docker-compose.yml file and want to put all ready made configurations in single Dockerfiles, and simply include them as "build" in the docker-compose.yml.
Also the dependency check is very simplistic and configuration dependent right now. Improving this, e.g. by connecting it to well-known repositories, is one
of the most important improvements this tool needs.
Furthermore, the tool can only succeed when there are more and more preconfigured setups available. Adding new configurations is a very important part for keeping this project alive.
Last but not least, we are thinking about a test run of all created Docker Compose stacks and executing them with Docker in Docker. The project would only be
delivered if the container starts without errors and can be called over SSH.

I am looking forward checking some contributions, and I will apologize in advance if I can't react like this was a full time project. I will still
try to react to each contribution in reasonable time. Have fun coding!