## adhocore/phint

Initializes new PHP project with sane defaults using templates.
It scaffolds PHP library &/or project to boost your productivity and save time.
It helps you be even more lazier! `phint` is work in progress and the plan is to make it [big](#todo).

[![Latest Version](https://img.shields.io/github/release/adhocore/phint.svg?style=flat-square)](https://github.com/adhocore/phint/releases)
[![Travis Build](https://img.shields.io/travis/adhocore/phint/master.svg?style=flat-square)](https://travis-ci.org/adhocore/phint?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/phint.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/phint/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/phint/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/phint)
[![StyleCI](https://styleci.io/repos/108550679/shield)](https://styleci.io/repos/108550679)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


## Installation

```bash
composer global require adhocore/phint
```

Make sure to place `$HOME/.composer/vendor/bin` in system PATH environment variable. Below is an example with zsh:

```bash
echo 'export PATH=$HOME/.composer/vendor/bin:$PATH' >> ~/.zshrc
source ~/.zshrc
```


## Usage

It can be used to quickly spin off new  project containing all basic and default stuffs. The quick steps are as follows:

```bash
# See options/arguments
phint init --help

# OR (shortcut)
phint i -h

# Below command inits a brand new PHP project in `project-name` folder in current dir
# Missing arguments are interactively collected
phint init project-name

# You can also use config file (with json) to read option values from
phint init project-name --config phint.json
```

## Commands

### init

> alias i

Help output:

```
Command init, version 0.0.3

Create and Scaffold a bare new PHP project

Usage: init [OPTIONS...] [ARGUMENTS...]

Arguments:
  <project>    The project name without slashes

Options:
  [-c|--config]         JSON filepath to read config from
  [-d|--descr]          Project description
  [-D|--dev]            Developer packages
  [-e|--email]          Vendor email
  [-f|--force]          Run even if the project exists
  [-h|--help]           Show help
  [-k|--keywords]       Project Keywords (`php`, `<project>` auto added)
  [-n|--name]           Vendor full name
  [-N|--namespace]      Root namespace
  [-p|--path]           The project path (Auto resolved)
  [-P|--php]            Minimum PHP version
  [-r|--req]            Required packages
  [-t|--type]           Project type
  [-u|--username]       Vendor handle/username
  [-z|--using]          Reference package
  [-v|--verbosity]      Verbosity level
  [-V|--version]        Show version
  [-y|--year]           License Year

Legend: <required> [optional]

Usage Examples:
  phint init <project> --force --description "My awesome project" --name "Your Name" --email "you@domain.com"
  phint init <project> --using laravel/lumen --namespace Project/Api --type project
  phint init <project> --php 7.0 --config /path/to/json --dev mockery/mockery --req adhocore/jwt --req adhocore/cli
```


![Phint Init Help](https://i.imgur.com/Ovjq5Dc.png "Phint Init")

## Example config

Parameters sent via command args will have higher precedence than values from config file.

What can you put in config? Anything but we suggest you put only known options (check `$ phint init --help`)

```
{
  "type": "library",
  "namespace": "Ahc",
  "username": "adhocore",
  "name": "Jitendra Adhikari",
  "email": "jiten.adhikary@gmail.com",
  "php": "7.0"
}
```

## Todo

Including but not limited to:

- [ ] Readme.md generator
- [ ] Test files generator
- [ ] Specify template path (with fallback to current)
