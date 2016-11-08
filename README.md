# ChainCommandBundle

ChainCommandBundle help you run your commands as chain of commands. You have opportunity to register console 

commands from Symfony bundles as members of command chain. You define main command and append other command 

as children with some priority. You can create one or several chains. When main command in chain is running
   
every command in chain will be accomplished in order your priority. 

Installation
---------------------------

###Step 1: Download the Bundle

Open a terminal, change directory to your project directory and run the
following command to download the this bundle:

```console
$ composer require syrotchukandrew/chain-command-bundle
```


or add to your composer.json file following:

```json
"require" : {
        "syrotchukandrew/chain-command-bundle" : "dev-master"        
    },    
"repositories" : [{
        "type" : "vcs",        
        "url" : "https://github.com/syrotchukandrew/ChainCommandBundle.git"        
    }],
```
    
and run command:

```console
$ composer update
```

###Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new ChainCommandBundle\ChainCommandBundle(),
        );

        // ...
    }

    // ...
}
```

How to use
-------------------------   
    
For adding command in chain of commands you should register command

as service with tag "chain_command" - after that command is member of chain.

Additional information as 'parent'='main' define your service as main command:

    tags:
        - { name: chain_command, parent: main, priority:  null}
        
or 'parent'='main_command_name' define your command as member of chain where 

main command's  name is 'main_command_name':

    tags:
        - { name: chain_command, parent: main_command_name, priority:  10}
        
priority define order executing commands in chain.

