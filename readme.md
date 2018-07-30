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

- Requires PHP7.

## Installation

### Manual

Download `phint.phar` from [latest release](https://github.com/adhocore/phint/releases/latest).
And use it like so `php /path/to/phint.phar [opts] [args]`. Hmm not cool. See Command section below.

### Command

```bash
# get latest version (you need `jq`)
LATEST_PHINT=`curl --silent "https://api.github.com/repos/adhocore/phint/releases/latest" | jq -r .tag_name`

# download latest phint
curl -sSLo ~/phint.phar "https://github.com/adhocore/phint/releases/download/$LATEST_PHINT/phint.phar"

# make executable
chmod +x ~/phint.phar
sudo ln -s ~/phint.phar /usr/local/bin/phint

# check
phint --help
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

Create and Scaffold a bare new PHP project.

***Parameters:***

```
Arguments:
  <project>  The project name without slashes

Options:
  [-c|--no-codecov]        Disable codecov
  [-C|--config]            JSON filepath to read config from
  [-d|--descr]             Project description
  [-D|--dev...]            Developer packages
  [-e|--email]             Vendor email
  [-f|--force]             Run even if the project exists
  [-h|--help]              Show help
  [-w|--keywords...]       Project Keywords (`php`, `<project>` auto added)
  [-L|--license]           License [m: mit | g: gnulgpl | b: bsd | a: apache2]. EG: `-L a`
  [-n|--name]              Vendor full name
  [-N|--namespace]         Root namespace (use `/` separator). EG: `-N ahc/phint`
  [-p|--path]              The project path (Auto resolved) [Deprecated]
  [-P|--php]               Minimum PHP version
  [-R|--req...]            Required packages
  [-s|--no-scrutinizer]    Disable scrutinizer
  [-l|--no-styleci]        Disable StyleCI
  [-t|--no-travis]         Disable travis
  [-T|--type]              Project type
  [-u|--username]          Vendor handle/username
  [-z|--using]             Reference package
  [-v|--verbosity]         Verbosity level
  [-V|--version]           Show version
  [-y|--year]              License Year

Usage Examples:
  phint init <project> --force --descr "Awesome project" --name "YourName" --email you@domain.com
  phint init <project> --using laravel/lumen --namespace Project/Api --type project</comment>
  phint init <project> --php 7.0 --config /path/to/json --dev mockery/mockery --req adhocore/cli
```

### Example config

Parameters sent via command args will have higher precedence than values from config file (`-C --config`).

What can you put in config? Anything but we suggest you put only known options (check `$ phint init --help`)

```json
{
  "type": "library",
  "namespace": "Ahc",
  "username": "adhocore",
  "name": "Jitendra Adhikari",
  "email": "jiten.adhikary@gmail.com",
  "php": "7.0",
  "codecov": false
}
```

## update

> alias u

Update Phint to lastest version or rollback to earlier locally installed version.

***Parameters:***

```
Arguments:
  (n/a)

Options:
  [-h|--help]         Show help
  [-r|--rollback]     Rollback to earlier version
  [-v|--verbosity]    Verbosity level
  [-V|--version]      Show version

Legend: <required> [optional] variadic...

Usage Examples:
  phint update        Updates to latest version
  phint u             Also updates to latest version
  phint update -r     Rolls back to prev version
  phint u --rollback  Also rolls back to prev version
```

## test

> alias t

Generate test files with proper classes and test methods analogous to their source counterparts.

***Parameters:***

```
Arguments:
  (n/a)

Options:
  [-a|--with-abstract]    Create stub for abstract/interface class
  [-d|--dump-autoload]    Force composer dumpautoload (slow)
  [-h|--help]             Show help
  [-n|--naming]           Test method naming format [t: testMethod | m: test_method | i: it_tests_]
  [-p|--phpunit]          Base PHPUnit class to extend from
  [-s|--no-setup]         Dont add setup method
  [-t|--no-teardown]      Dont add teardown method
  [-v|--verbosity]        Verbosity level
  [-V|--version]          Show version

Usage Examples:
  phint test -n i        With `it_` naming
  phint t --no-teardown  Without `tearDown()`
  phint test -a          With stubs for abstract/interface
```


## Todo

Including but not limited to:

- [ ] Readme.md generator
- [x] Test files generator
- [ ] Specify template path (with fallback to current)
