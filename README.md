## adhocore/phint

Initializes new PHP project with sane defaults using templates.
It scaffolds PHP library &/or project to boost your productivity and save time.

For already existing project, run with `--sync` flag to add missing stuffs, see [phint init](#init).

Once you have files in your `src/` or `lib/` you can run [phint docs](#docs) to generate API like documentation in `.md` format
and [phint test](#test) to generate basic test stubs with all the structures already maintained.

It helps you be even more lazier! **phint** is continuously evolving and the plan is to make it [big](#todo).

[![Latest Version](https://img.shields.io/github/release/adhocore/phint.svg?style=flat-square)](https://github.com/adhocore/phint/releases)
[![Travis Build](https://img.shields.io/travis/adhocore/phint/master.svg?style=flat-square)](https://travis-ci.org/adhocore/phint?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/phint.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/phint/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/phint/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/phint)
[![StyleCI](https://styleci.io/repos/108550679/shield)](https://styleci.io/repos/108550679)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Donate 15](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square&label=donate+15)](https://www.paypal.me/ji10/15usd)
[![Donate 25](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square&label=donate+25)](https://www.paypal.me/ji10/25usd)
[![Donate 50](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square&label=donate+50)](https://www.paypal.me/ji10/50usd)
[![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=Scaffold+new+PHP+project+with+sane+defaults+using+templates&url=https://github.com/adhocore/phint&hashtags=php,template,scaffold,initproject)


![Phint Preview](https://imgur.com/F6PkX9Z.png "Phint Preview")

[Installation](#installation) &middot; [Features](#features) &middot; [Autocompletion](#autocompletion) &middot; [Usage](#usage) &middot; [phint init](#init) &middot; [phint update](#update) &middot; [phint docs](#docs) &middot; [phint test](#test) &middot; [Templating](#templating)

> Phint is powered by [adhocore/cli](https://github.com/adhocore/php-cli)

## Installation

> Requires PHP7.

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

## Features

- generate dot files the likes of `.gitignore, .travis.yml, . editorconfig` etc
- generate `LICENSE`, `README.md`, `composer.json`
- generate `CHANGELOG.md` stub, `CONTRIBUTING.md` guide, `ISSUE_TEMPLATE.md` and `PULL_REQUEST_TEMPLATE.md`
- generate binaries if any
- git init
- interactively ask and install all the dev and prod deps
- generate `phpunit.xml`, test `bootstrap.php`
- generate test stubs for all classes/methods corresponding to `src` (`phint test`)
- generate docs for all public class/methods
- export templates to chosen path so it can be customized (`phint export`)
- use custom templates from a path specified by user
- update its own self (`phint update`)

## Autocompletion

The phint commands and options can be [autocompleted](https://github.com/adhocore/php-cli#autocompletion) if you use zsh shell with oh-my-zsh.

Setting up auto complete:

```sh
mkdir -p ~/.oh-my-zsh/custom/plugins/ahccli && cd ~/.oh-my-zsh/custom/plugins/ahccli

[ -f ./ahccli.plugin.zsh ] || curl -sSLo ./ahccli.plugin.zsh https://raw.githubusercontent.com/adhocore/php-cli/master/ahccli.plugin.zsh

echo compdef _ahccli phint >> ./ahccli.plugin.zsh

chmod +x ./ahccli.plugin.zsh && source ./ahccli.plugin.zsh && cd -
```

Dont forget to [add](https://github.com/adhocore/php-cli#load-ahccli-plugin) `ahccli` into `plugins=(... ...)` list in `~/.zshrc` file.

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

Each of the commands below should be used like so:
```sh
cd /path/to/project
phint <command> [--options] [args]
```

### init

> alias i

Create and Scaffold a bare new PHP project.

***Parameters:***

Dont be intimidated by long list of parameters, you are not required to enter any of them
as arguments as they are interactively collected when required.

Also check [config](#example-config) on how to create a reusable json config so you can use `phint` like a *pro*.

```
Arguments:
  <project>  The project name without slashes

Options:
  [-b, --bin...]            Executable binaries
  [-c, --no-codecov]        Disable codecov
  [-C, --config]            JSON filepath to read config from
  [-d, --descr]             Project description
  [-D, --dev...]            Developer packages
  [-e, --email]             Vendor email
  [-f, --force]             Run even if the project exists
  [-G, --gh-template]       Use `.github/` as template path
                            By default uses `docs/`
  [-h, --help]              Show help
  [-w, --keywords...]       Project Keywords
  [-L, --license]           License (m: MIT, g: GNULGPL, a: Apache2, b: BSDSimple, i: ISC, w: WTFPL)
  [-n, --name]              Vendor full name
  [-N, --namespace]         Root namespace (use `/` separator)
  [-g, --package]           Packagist name (Without vendor handle)
  [-p, --path]              The project path (Auto resolved)
  [-P, --php]               Minimum PHP version
  [-R, --req...]            Required packages
  [-s, --no-scrutinizer]    Disable scrutinizer
  [-l, --no-styleci]        Disable StyleCI
  [-S, --sync]              Only create missing files
                            Use with caution, take backup if needed
  [-t, --no-travis]         Disable travis
  [-T, --type]              Project type
  [-u, --username]          Vendor handle/username
  [-z, --using]             Reference package
  [-y, --year]              License Year

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
  "codecov": false,
  "...": "..."
}
```

---
## update

> alias u

Update Phint to lastest version or rollback to earlier locally installed version.

***Parameters:***

```
Options:
  [-h, --help]         Show help
  [-r, --rollback]     Rollback to earlier version

Usage Examples:
  phint update        Updates to latest version
  phint u             Also updates to latest version
  phint update -r     Rolls back to prev version
  phint u --rollback  Also rolls back to prev version
```

---
## docs

> alias d

Generate docs (`.md`) for all public classes and methods from their docblocks.

Ideally you would run it on existing project **or** after you create/update `src/` files.

***Parameters:***

```
Options:
  [-a, --with-abstract]    Create docs for abstract/interface class
  [-h, --help]             Show help
  [-o, --output]           Output file (default README.md). For old project you should use something else
                           (OR mark region with <!-- DOCS START --> and <!-- DOCS END --> to inject docs)

Usage Examples:
  phint docs               If there is `<!-- DOCS START -->` and `<!-- DOCS END -->` region
                           Injects new doc in between them otherwise appends to bottom
  phint d -o docs/api.md   Writes to docs/api.md (Same rule applies regarding inject/append)
```

### Sample docs

***PHP code***

```php
namespace Abc;

/**
 * This is dummy class.
 *
 * It does nothing as of now.
 * Maybe you could fix it?
 */
class Dummy
{
    /**
     * Alpha beta.
     *
     * Example:
     *
     * <code>
     * $dummy = new Dummy;
     * $dummy->alpha('john', true);
     * // '...'
     * </code>
     *
     * @param string $name
     * @param bool   $flag
     *
     * @return string|null
     */
    public function alpha($name, $flag)
    {
        //
    }
}
```

***Generated Markdown***

```md
## Dummy

```php
use Abc\Dummy;
\```

> This is dummy class.

It does nothing as of now.
Maybe you could fix it?

### alpha()

> Alpha beta.

```php
alpha(string $name, bool $flag): string|null
\```

Example:

```php
$dummy = new Dummy;
$dummy->alpha('john', true);
// '...'
\```
```

***Preview***

## Dummy

```php
use Ahc\Dummy;
```

> This is dummy class.

It does nothing as of now.
Maybe you could fix it?

### alpha()

> Alpha beta.

```php
alpha(string $name, bool $flag): string|null
```

Example:

```php
$dummy = new Dummy;
$dummy->alpha('john', true);
// '...'
```

---
## test

> alias t

Generate test files with proper classes and test methods analogous to their source counterparts.
If a test class already exists, it is skipped. In future we may append test stubs for new methods.

Ideally you would run it on existing project **or** after you create/update `src/` files.

***Parameters:***

```
Options:
  [-a, --with-abstract]    Create stub for abstract/interface class
  [-h, --help]             Show help
  [-n, --naming]           Test method naming format
                           (t: testMethod | m: test_method | i: it_tests_)
  [-p, --phpunit]          Base PHPUnit class to extend from
  [-s, --no-setup]         Dont add setup method
  [-t, --no-teardown]      Dont add teardown method

Usage Examples:
  phint test -n i        With `it_` naming
  phint t --no-teardown  Without `tearDown()`
  phint test -a          With stubs for abstract/interface
```

### Sample test

Generated `tests/Dummy.php` for [Abc\\Dummy](#sample-docs) above:

```php
<?php

namespace Abc\Test;

use Abc\Dummy;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Auto generated by `phint test`.
 */
class DummyTest extends TestCase
{
    /**
     * @var Dummy
     */
    protected $dummy;

    public function setUp()
    {
        parent::setUp();

        $this->dummy = new Dummy;
    }

    public function testAlpha()
    {
        $actual = $this->dummy->alpha();

        // $this->assertSame('', $actual);
    }
}
```

---
## Templating

> `phint export --to ~/myphint`

So you would like to have your own templates and customize `phint` to your taste!

First you need to create a directory root (of any name, eg: `myphint`) with structure that looks like:

```tree
myphint
├── CHANGELOG.md.twig
├── composer.json.twig
├── CONTRIBUTING.md.twig
├── docs
│   ├── docs.twig
│   ├── ISSUE_TEMPLATE.md.twig
│   └── PULL_REQUEST_TEMPLATE.md.twig
├── .editorconfig.twig
├── .env.example.twig
├── .gitignore.twig
├── LICENSE.twig
├── package.json.twig
├── phpunit.xml.dist.twig
├── README.md.twig
├── tests
│   ├── bootstrap.php.twig
│   └── test.twig
└── .travis.yml.twig
```

Note that you dont need to have all the files there in new directory just pick the ones you would like to customize and start hacking.

Luckily you **dont** have to create these templates yourself, just run `phint export --to ~/myphint`!

**Pro Tip**
You can actually introduce any new template as long as their extension is `.twig`.
Such templates are *only* used by `phint init` command. Check [Template variables](#template-variables).

After you are done customizing these templates you can use them in each of the *phint* commands like so

```sh
phint init project --template ~/myphint
phint docs --template ~/myphint
phint test --template ~/myphint
```

The short option name for `--template` is `-x`.

#### Template variables

Here's what parameters these templates would receive when run:

- ***[docs/docs.twig](https://github.com/adhocore/phint/blob/master/resources/docs/docs.twig):*** classes [metadata](#class-metadata) and [docs parameters](#docs)
- ***[tests/test.twig](https://github.com/adhocore/phint/blob/master/resources/tests/test.twig):*** class [metadata](#class-metadata) and [test parameters](#test)
- ***Everything else:*** [init parameters](#init)

***Metadata***

- The `docs` and `test` commands read and use source files metadata.
- The `docs.twig` template recieves metadata collection of all classes at once.
- The `test.twig` template recieves metadata unit of one class at a time.

### Class metadata

Example metadata for [Abc\\Dummy](#sample-docs) above:

```php
[
  'namespace'   => 'Abc',
  'classFqcn'   => 'Abc\\Dummy',
  'classPath'   => '/home/user/projects/src/Dummy.php',
  'name'        => 'Dummy',
  'className'   => 'Dummy',
  'isTrait'     => false,
  'isAbstract'  => false,
  'isInterface' => false,
  'newable'     => true,
  'title'       => 'This is dummy class.',
  'texts'       => [
    'It does nothing as of now.',
    'Maybe you could fix it?',
  ],
  'methods' => [
    'alpha' => [
      'name'       => 'alpha',
      'inClass'    => 'Abc\\Dummy',
      'isStatic'   => false,
      'isFinal'    => false,
      'isPublic'   => true,
      'isAbstract' => false,
      'maybeMagic' => false,
      'title'      => 'Alpha beta.',
      'texts'      => [
        'Example:',
        '<code>',
        '$dummy = new Dummy;',
        '$dummy->alpha(\'john\', true);',
        '// \'...\'',
        '</code>',
      ],
      'return' => 'string|null',
      'params' => [
        'string $name',
        'bool $flag',
      ],
    ],
    // more methods ...
  ],
];
```

## Todo

Including but not limited to:

- [x] README.md/Docs generator
- [x] Test files generator
- [x] Support user templates
- [ ] Test stubs for new methods

## License

> &copy; 2017-2020, [Jitendra Adhikari](https://github.com/adhocore) | [MIT](./LICENSE)

### Credits

This library is release managed by [please](https://github.com/adhocore/please).
