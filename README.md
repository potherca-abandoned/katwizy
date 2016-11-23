# [Katwizy] - A cleaner Symfony install for light-weight projects

## 🔔 Introduction

> 🌟 Helping your project make a clean finish. 🏁🚗💨

### 🎯 Project Goals

Katwizy hat the following goals:

- Project code should be the _only_ code in a project repository
- *All* vendor code should live in the `vendor` directory<sup>1</sup>
- All files that need to be in a project directory should be _generated_ and `.gitignore`'d
- A projects composer file should be clean and as free from framework entries as possible
- Katwizy should only be a thin layer between a project and Symfony.
- Functionality should be configurable
- Configuration should not be done in code

<sup>1</sup> This includes _all_ Symfony code as well.

## 🏗 Installation

Install the Katwizy [package] through composer:

    composer require potherca/katwizy

This will also install [the Symfony framework]<sup>2</sup>.

<sup>2</sup> And all of the bundles that come with [the standard Symfony install].

## 🌟 Usage

Katwizy hides all of the standard Symfony files out of sight, so your project
only has to implement the parts that it needs.

### Minimal example

The absolute minimum that is required to get started is a `web` folder that
contains an `index.php` file that does the following:

1. Get the Composer Autoloader
2. Declare a Controller (including a Route)
3. Run the bootstrap

Such a file could look like this:

    // web/index.php
    <?php

    $loader = require dirname(__DIR__).'/vendor/autoload.php';

    class Website extends Symfony\Bundle\FrameworkBundle\Controller\Controller
    {
        /**
         * @Sensio\Bundle\FrameworkExtraBundle\Configuration\Route("/")
         * @Sensio\Bundle\FrameworkExtraBundle\Configuration\Route("/{name}")
         */
        final public function homepage($name = 'world')
        {
            return new Symfony\Component\HttpFoundation\Response(
                sprintf('<p>Hello %s!</p>', $name)
            );
        }
    }

    Potherca\Katwizy\Bootstrap::run(
        $loader,
        Symfony\Component\HttpFoundation\Request::createFromGlobals()
    );

    /*EOF*/

There is [a separate repository with this example](https://github.com/Katwizy/example-minimum)

### Next steps

There are several things that could be done next:

- Declare the Controller in a separate file. All files in the `src` directory
  are also scanned for Route annotations
- Declare routes in a separate config file, rather than using annotations.

### Configuration

There are various things that can be configured by adding certain files to the
`config` directory:

#### Bundles

Bundles can be added to the AppKernel from a `bundles.yml` file. It is not
needed (or possible) to edit the AppKernel. Separate sections must be defined
for `prod`, `test` and `dev` bundles. All bundles defined in the `prod` section
will also be loaded in `test` and `dev` environments.

#### General Configuration

General configuration can be managed from a separate `config.yml` file.

#### Parameters

Parameters that are to be used in other configuration files can be stored in a
`parameters.yml` file. This file will be loaded before the other configuration
files.

#### Emulating Environmental Variables

Besides the parameters file, it is also possible to use an so called `.env` file.

This is done by adding a file named `.env` to a projects root directory. Entries
in the `.env` file will be loaded for use from `getenv()`/`$_ENV`/`$_SERVER`.

For variables in the `.env` file need to be loaded in the Symfony configuration,
they need to be prefixed with `SYMFONY__`, as [described in the Symfony cookbook](http://symfony.com/doc/current/configuration/external_parameters.html#environment-variables).

To have a period "." in the config name, use a double underscore "__" in the
variable name. (Double underscores will be replaced by a period, as a period is
not a valid character in an environment variable name). Variables can also be
nested by wrapping an existing environment variable in `${…}`

For instance:

    BASE_DIR="/var/webroot/project-root"
    CACHE_DIR="${BASE_DIR}/cache"
    TMP_DIR="${BASE_DIR}/tmp"

#### Routes

Routes can be configured from a `routes.yml` file.

#### Security
Security can be configured from a `security.yml` file. A security configuration
from the Symfony Standard Edition is already loaded by default.

#### Services

Services can be configured from a `services.yml` file.

### Composer Script Commands

It is not uncommon for the `scripts` section in a `composer.json` file to grow
quite large. A simple solution is to create a script that holds all of the 
entries that should be called.

Katwizy provides an abstract base class `AbstractScriptEventHandler` that makes 
it trivial to add commands to be called from the Composer Scripts.

All that an extending classes has to do is implement the `getCommands` function
and add the command class to the `script` section in the project's `composer.json` 
file.

#### Extending the `AbstractScriptEventHandler`

An implementation might look something like this:

    <?php
    
    use Composer\Script\ScriptEvents;
    use Composer\Script\Event;
    use Potherca\Katwizy\Command\ImmutableCommand;
    use Potherca\Katwizy\Command\ScriptEventHandler;
    
    class ScriptEventHandler extends ScriptEventHandler
    {
        /**
         * Return commands to be run after composer install/update.
         *
         * To only call certain scripts on specific events, get the event name (with
         * `$event->getName()`) and check it against the available events in
         * Composer\Script\ScriptEvents. The most commonly used events are:
         *
         * - ScriptEvents::POST_INSTALL_CMD
         * - ScriptEvents::POST_UPDATE_CMD
         *
         * Command provided by the Symfony `console` command should be marked as
         * `COMMAND_TYPE_SYMFONY`. These will be handled the same as COMMAND_TYPE_SHELL
         *
         * @param Event $event
         *
         * @return ImmutableCommand[]
         *
         * @throws \InvalidArgumentException
         */
        final public function getCommands(Event $event)
        {
             $commands = [];
            
            if (in_array($event->getName(), [ScriptEvents::POST_UPDATE_CMD, ScriptEvents::POST_INSTALL_CMD])) {
                $commands[] =  new ImmutableCommand(
                    ImmutableCommand::COMMAND_TYPE_SYMFONY, 
                    'assets:install'
                );
            }
            
            if ($event->getName() === ScriptEvents::POST_UPDATE_CMD) {
                $commands[] = new ImmutableCommand(
                    ImmutableCommand::COMMAND_TYPE_PHP, 
                    'Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache'
                );
            }
            
            return $commands;
        }
    }
    
    /*EOF*/

#### Adding script to `composer.json`

After the Commad class has been created it can be added to the composer file like this:


    {
        "require": {
            "…": "…"
        }
        "scripts": {
            "post-install-cmd": "\\ScriptEventHandler::handleEvent",
            "post-update-cmd": "\\ScriptEventHandler::handleEvent"
        }
    }


### More details on configuration

As `routes.yml`, `security.yml` and `services.yml` are loaded separately, there
is no need to `include` them from the general config file.

For `config.yml`, `parameters.yml`, `routes.yml`, `security.yml` and `services.yml`
files, Katwizy will first look for a environment specific file before looking for
a generic file. For instance, for a config file Katwizy will first look for a
`config_prod.yml` file (or `config_test.yml` or `config_dev.yml`, depending on
the environment). If that is not found it will look for a `config.yml` file.

This allows for creating a generic configuration that can be included from
environment specific files. Any environment that does not have a specific congif
file will default to the generic config file.

### Available commands

- The Symfony `console` command is available from the vendor `bin` directory
  This is `vendor/bin/` by default (but this can be [configured from `composer.json`])
- The `flotsam` command (also in the vendor `bin` directory) can be run to
  symlink other functionality from the Symfony Standard Edition into the project
  directory (and add them to the `.gitignore` file).


## 🤖 How it works

Katwizy implements a custom Kernel which changes where Symfony looks for things.

More information about how this works can be found in the Symfony manual pages
about [the micro kernel trait], [overriding Symfony directory structure] and
[Symfony Kernel Configuration].


## 📝 Other information

### ©️ License

The Source Code for this project is available under a
[GPL-3.0+ License][GPL-3.0+] (GNU General Public License v3.0 or higher) –
Created by [Potherca]

### 💡 Origin / Motivation

The Symfony manual offers various ways to get started with a new project.

They all (more or less) lead to a script that dumps a bunch<sup>3</sup> of files
in a directory of your choice.

The manual then tells you to just go ahead and commit all that mess into git.
For small projects and proof-of-concepts this is basically a lot of vendor code
poluting your clean new code base.

A large part of these files will never be edited, leaving your repository strewn
with files that have "Initial commit" as message. Forever.

As a final insult, the created `composer.json` file is full of verbose entries
that could be a lot shorter if other means were used<sup>4</sup>.

There must be a better way.

Katwizy tries to offer this "better way".

<sup>3</sup> To be precis: 38 files in 18 folders.
<sup>4</sup> Like a composer metapackage, a single file to add composer-scripts
to, a Symfony composer plugin, a config file to link to from the `extra` section.

### 🤔 About the name

Katwizy offers lightweight transportation.The name is a portmanteau of two
light-weight car models. The Ford Ka and the Renault Twizy.

It is *not* a Polish translation of "Cat Visa". That was just a happy coincidence.

## 💮 About the logo

## ❓FAQ

### ❔What is wrong with Symfony code in your project?

The way that Symfony is structured (like most frameworks these days) is in such
a way that the directories don't actually show anything about the domain the
application is involved with. There is [a keynote by Uncle Bob] from the Ruby
Midwest 2011 conference that explains the problem this poses in quite some details.

### ❔Why not simply use Silex?

The most commonly suggested solution to have a "lightweight Symfony" is to use
[Silex]. There is, however, one problems with that... Silex is _not_ Symfony.
This means that yet another framework needs to be learned, Bundles will _not_
work<sup>5</sup> and the problem shifts from one framework-oriented directory
structure to another framework's directory structure. So... no thanks.

<sup>5</sup> And no, not all functionality from bundles are available in Silex
through other means.

### ❔ Which packages/bundles are installed?

The following bundles and packages are installed with Katwizy:

#### Packages

 - **vlucas/phpdotenv** - Loads environment variables from `.env` to `getenv()`/`$_ENV`/`$_SERVER` automagically.

#### Bundles

 - **DoctrineBundle** - Adds support for the Doctrine ORM
 - **FrameworkBundle** - The core Symfony framework bundle
 - **MonologBundle** - Adds support for Monolog, a logging library
 - **SecurityBundle** - Adds security by integrating Symfony's security component
 - **SensioFrameworkExtraBundle** - Adds several enhancements, including template and routing annotation capability
 - **SwiftmailerBundle** - Adds support for Swiftmailer, a library for sending emails
 - **TwigBundle** - Adds support for the Twig templating engine

#### Only in in `dev`/`test` environment

 - **DebugBundle** - Adds Debug and VarDumper component integration
 - **SensioDistributionBundle** - Adds functionality for configuring and working with Symfony distributions
 - **SensioGeneratorBundle** - Adds code generation capabilities
 - **WebProfilerBundle** - Adds profiling functionality and the web debug toolbar

### ❔Whats with all of the emoji's and footnotes?

I don't like my documentation boring and colourless. That's all.

[Katwizy]: https://github.com/Potherca/Katwizy/
[package]: https://packagist.org/packages/potherca/katwizy
[the Symfony framework]: https://Symfony.com/
[the standard Symfony install]: https://github.com/Symfony/Symfony-standard
[the micro kernel trait]: https://Symfony.com/doc/current/configuration/micro_kernel_trait.html
[overriding Symfony directory structure]: https://Symfony.com/doc/current/configuration/override_dir_structure.html
[Symfony Kernel Configuration]: https://Symfony.com/doc/current/reference/configuration/kernel.html
[GPL-3.0+]: ./LICENSE
[Potherca]: https://pother.ca/
[a keynote by Uncle Bob]: https://www.youtube.com/watch?v=hALFGQNeEnU
[Silex]: https://silex.sensiolabs.org/
[configured from `composer.json`]: https://getcomposer.org/doc/06-config.md#bin-dir
