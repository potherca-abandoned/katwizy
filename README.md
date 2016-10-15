# [Katwizy] - A cleaner Symfony install for light-weight projects

## 🔔 Introduction

 🏁🚗💨

> Helping your project make a clean finish.

### 🎯 Project Goals

Katwizy hat the following goals:

- All project code should be the _only_ code in your repository
- All Symfony code should live in the `vendor` directory
- Katwizy should only be a thin layer between project code and Symfony.
- Functionality should be configurable
- Configuration should not be done in code

## 🏗 Installation

Install [package] through composer:

    composer require potherca/katwizy

This will also install [the Symfony framework].

## Usage

    @TODO: Explain about configuration and getting started

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
They all (more or less) lead to a script that dumps a bunch of files in a
directory of your choice.

It then politely tells you to just go ahead and commit all that mess into git as
if nothing happened. For small projects and proof-of-concepts this is a
basically a lot of vendor code poluting your clean new code base.

There must be a better way.

Katwizy tries to offer this "better way".

### 🤔 About the name

Katwizy offers lightweight transportation.The name is a portmanteau of two
light-weight car models. The Ford Ka and the Renault Twizy.

It is *not* a Polish translation of "Cat Visa". That was just a happy coincidence.

[Katwizy]: https://github.com/potherca/Katwizy/
[package]: https://packagist.org/packages/potherca/katwizy
[the Symfony framework]: https://symfony.com/
[the micro kernel trait]: http://symfony.com/doc/current/configuration/micro_kernel_trait.html
[overriding Symfony directory structure]: http://symfony.com/doc/current/configuration/override_dir_structure.html
[Symfony Kernel Configuration]: http://symfony.com/doc/current/reference/configuration/kernel.html
[GPL-3.0+]: ./LICENSE
[Potherca]: http://pother.ca/

