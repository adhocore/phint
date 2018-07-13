## adhocore/phint

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

![Phint Init Help](https://i.imgur.com/Ovjq5Dc.png "Phint Init")

## Example config

Anything in config will have higher precedence

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

- [ ] Test files generator
