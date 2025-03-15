# Laravel Safe Migrations
The Laravel Safe Migrations package helps you detect migrations you have changed but already exist in the remote repository. The package will also help you detect migrations that have been changed by other programmers but have already been migrated to your local environment. The package tracks changes based on Git.

## Installation
~~~
composer require --dev aramayismirzoyan/laravel-safe-migrations
~~~
## Commands

### check:migrations

This command checks the migrations you changed, and they are already in the remote repository.

~~~
php artisan check:migrations
~~~

You can also specify specific branches and remotes. You must specify the remote branches on which you want to check for the existence of the migration.
If you specify branches and remotes the check will be much faster.
~~~
php artisan check:migrations main branch2 --remote=origin --remote=local
~~~

To update data from a remote repository, set the --fetch flag.
~~~
php artisan check:migrations --fetch
~~~

You can also run this command via ./vendor/bin/safemigrations
~~~
./vendor/bin/safemigrations check
~~~
Specify branches and remotes.
~~~
./vendor/bin/safemigrations check branches=main,branch2 remotes=origin,local
~~~

### check:migrated
The command checks if you have migrated migrations that have been changed by other programmers. The command checks the changes you have pulled using the `git pull` command.
~~~
php artisan check:migrated
~~~

If you do not specify a commit hash, then the commit you pulled using the `git pull` command will be checked. You can also specify the hash of the commit you want to check.

~~~
php artisan check:migrated commit_hash
~~~

## GitHub Actions

You can also run migration checking on your GitHub Actions. You need to have this configuration: 

~~~
    steps:
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    - uses: actions/checkout@v4
      with:
        fetch-depth: ${{ github.event_name == 'pull_request' && 2 }}
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    - name: Check migrations
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
      run: php artisan github:actions <main_branch>
~~~

In the `php artisan github:actions` command you need to pass your main branch as an argument. The check will only happen during a pull request and will check for migrations on the master branch. If there is at least one sensitive migration pipeline that will fail.