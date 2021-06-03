
Requirments
----------------------

* Phing
* php cli
* PEAR (with a version of phpunit supported by silverstripe)

Installation
----------------------

* Edit scripts/build.xml and change the <project> name attribute to <projectname>
* Copy scripts/build.properties.sample to scripts/build.properties
* Edit build.properties and change the DB configuration and rewrite.base settings.
* Run `phing -f scripts/build.xml` to make sure everything installs
* If you require additional modules, add them into the scripts/dependent-modules file, then run `phing -f scripts/build.xml update_modules`
* Run `phing -f scripts/build.xml test` to make sure everything's working as expected


## Phing targets

Commonly used targets

* build
* test
* package

Helpful for development: 

* `pak` - builds a .sspak file at mysite/build/site.sspak . Useful for CI 
* `phpstan` - Runs [PHPStan](https://github.com/phpstan/phpstan) + [PHPStan for SilverStripe](https://github.com/silbinarywolf/silverstripe-phpstan) if it's installed via Composer. Useful for static analysis of PHP code.
    - Define folders to scan by placing following in your build.xml file, underneath the `<project>` tag.
        - `<property name="phpstan.dir" value="mysite/src mysite/tests" />`


## Composer scripts

If your themes use yarn based dependencies, you can add the following post-install 
scripts to be triggered by a composer call, which will ensure the node\_modules
are installed correctly

```
    "scripts": {
		"post-update-cmd": [		
	            "Symbiote\\Build\\ThemeBuilder::run"
		],
		"post-install-cmd": [
	            "Symbiote\\Build\\ThemeBuilder::run"
		]
	},
```

Optional Scripts
----------------------

There are three scripts that may optionally be used for your projects, and can be done using the following commands.

### sh build/scripts/cache

This will clear out any project cache files (for all projects), and is basically the forced equivalent of doing a ?flush for everything in your site.

### sh ~/path/to/permissions

This requires you to update the "{user}" and as such will need to be copied out to a location of your choice, and will apply the appropriate owner and permissions to both the cache and repository.

### sh build/scripts/recursive-status

This will recursively trigger a "git status" on each module directory found within your repository, primarily so you check for changes that may have been made in a module that hasn't been included in the repository code base.

### sh build/scripts/recursive-status assume-unchanged-listing

When you have patched files (resulting in them coming up as being modified during a recursive-status), you can `git update-index --assume-unchanged {file_name}`, and use this script with a parameter. This will not only list out the files that have been assumed unchanged, but it will also force the files to reflect the upstream. This makes sure you don't have local changes that have been accidentally made, however it will also remove any patches that were put in place. Therefore, you can run this and then `phing` to ensure the patches remain in place.


## Excluding files from packages

Create a {root}/.pkgignore file with a set of paths to be excluded from built up package files

```
vendor/un-used/module/
themes/mytheme/node_modules/
mysite/cypress/

```
