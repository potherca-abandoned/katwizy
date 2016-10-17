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

Katwizy hides all of the standard symfony files out of sight, so your project
only has to implement the parts that it needs.

The absolute minimum that is required to get started is a `web` folder that
contains an `index.php` file that does the following:

1. Get the Composer Autoloader
2. Create a Controller
3. Run the bootstrap

Such a file would look like this:

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

@TODO: Explain more about configuration and getting started

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
to, a symfony composer plugin, a config file to link to from the `extra` section.

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

### ❔Whats with all of th eamoji's and footnotes?

I don't like my documentation boring and colourless. That's all.

[Katwizy]: https://github.com/Potherca/Katwizy/
[package]: https://packagist.org/packages/potherca/katwizy
[the Symfony framework]: https://symfony.com/
[the standard Symfony install]: https://github.com/symfony/symfony-standard
[the micro kernel trait]: https://symfony.com/doc/current/configuration/micro_kernel_trait.html
[overriding Symfony directory structure]: https://symfony.com/doc/current/configuration/override_dir_structure.html
[Symfony Kernel Configuration]: https://symfony.com/doc/current/reference/configuration/kernel.html
[GPL-3.0+]: ./LICENSE
[Potherca]: https://pother.ca/
[a keynote by Uncle Bob]: https://www.youtube.com/watch?v=hALFGQNeEnU
[Silex]: https://silex.sensiolabs.org/
