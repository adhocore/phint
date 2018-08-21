## How to contribute

### Before you start

Please be considerate that:

- Commit messages should follow angular standard

### Setting up

You may need to fork this project in [GitHub](https://github.com/adhocore/phint).

```sh
git clone git@github.com:adhocore/phint.git

# OR if you have a fork
# git clone git@github.com:<your_github_handle>/phint.git

cd phint

# Create a new branch
git checkout -b $branch_name

# Install deps
composer install -o
```

### Moving forward

```sh
# Open phint in IDE
subl phint

# ... and do the needful

# Optionally run the lint
for P in src tests; do find $P -type f -name '*.php' -exec php -l {} \;; done

# ... and phpcs fixer or stuffs like that!

# Run tests
vendor/bin/phpunit --coverage-text
```

### Finalizing

Everything looking good?

```sh
# Commit your stuffs
git add $file ...$files
git commit -m "..."

# Push 'em
git push origin HEAD
```

Now goto [GitHub](https://github.com/adhocore/phint/compare?expand=1), select your branch and create PR.

### Getting PR merged

You have to wait. You have to address change requests.

Thank you for contribution!
