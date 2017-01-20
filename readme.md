# Craft Installer

Install Craft CMS from the command line.

## Installation

```bash
composer global require geoffselby/craft-installer
```

## Upgrading

```bash
composer global update geoffselby/craft-installer
```

## Usage

Make sure `~/.composer/vendor/bin` is in your terminal's path.

```bash
cd ~/Sites
craft new awesome-new-craft-site
```

After entering your database credentials, the installer will download the latest version of Craft and install it in the directory you provided.
Sit back and relax because it may take a minute or two.

## Acknowledgements

Inspired by [Laravel Installer](https://github.com/laravel/installer)

## License

Craft Installer is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).