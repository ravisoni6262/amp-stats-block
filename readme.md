# AMP Statistics Block

This Wordpress plugin adds a block to the Wordpress' Gutenberg editor to display AMP statistics (validated pages, errors).

## Requirements

- WordPress 5.0+ or the [Gutenberg Plugin](https://wordpress.org/plugins/gutenberg/).
- [Composer](https://getcomposer.org) and [Node.js](https://nodejs.org) for dependency management.
- [Vagrant](https://www.vagrantup.com) and [VirtualBox](https://www.virtualbox.org) for local development environment.


## Development

1. Clone the plugin repository.

2. Setup the development environment and tools using [Node.js](https://nodejs.org) and [Composer](https://getcomposer.org):

	   npm install

3. Start a virtual testing environment using [Vagrant](https://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/):

	   vagrant up

	which will be available at [blockextend.local](http://blockextend.local) after provisioning (username: `admin`, password: `password`).

	Alternatively, run it on your local Docker host:

	   docker-compose up -d

	which will make it available at [localhost](http://localhost).

4. When installing AMP plugin, you may get popup to input FTP credentials, to remove it:

	Go to the container named 'wordress:latest':

	   docker exec -it {container_id} /bin/bash
	
	Open wp-config.php (you may need to install `nano` or any other editor of your choice) and place this line of code:

	   `define('FS_METHOD','direct');`

	The FTP popup will disappear. If you face a permission issue, change the owner-group of the `wp-content` directory to `www-data`:
	
	   chown www-data:www-data -R .

### Scripts

We use `npm` as the canonical task runner for the project. Some of the PHP related scripts are defined in `composer.json`.

- `npm run build` to build the plugin JS and CSS assets. Use `npm run dev` to watch and re-build as you work.

- `npm run lint:js` to lint JavaScript files with [eslint](https://eslint.org/).

- `npm run lint:php` to lint PHP files with [phpcs](https://github.com/squizlabs/PHP_CodeSniffer).

- `npm run test:php:no-coverage` to run PHPUnit tests without checking the coverage.
