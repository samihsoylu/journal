# Journal
Journal is a privacy first, self hosted digital log book. It is designed to be accessible anywhere you go, allowing you to organize your thoughts, feelings and opinions in one place. Your Journals are stored with AES 256 encryption using your own password as the encryption key, resulting in a protected and inaccessible log book from outsiders.

<img src="https://samihsoylu.nl/downloads/dashboard-journal.png">

## Feature highlights
* Mobile friendly
* Entries and Templates have AES-256 Encryption
* Format entry content using markdown
* Organise entries using categories
* Quickly add entries by enabling the quick-add widget
* Create predefined sets of templates for entries
* Advanced search 
* Unlimited users

## Requirements
* A Linux-based server with shell access
* PHP 7.4 or later.
* MySQL 5.6+

### PHP Extensions

The listed extensions are usually installed and enabled by default in most PHP 7 installations

* [json](https://www.php.net/manual/en/book.json.php)
* [pdo](https://www.php.net/manual/en/book.pdo.php)
* [openssl](https://www.php.net/manual/en/book.openssl.php)
* [tokenizer](https://www.php.net/manual/en/book.tokenizer.php)
* [mbstring](https://www.php.net/manual/en/book.mbstring.php)
* [ctype](https://www.php.net/manual/en/book.ctype)
* [pcre](https://www.php.net/manual/en/book.pcre)
* [session](https://www.php.net/manual/en/book.session)

## Installation

### 1. Get the .zip file

Download the zip package from the [releases page](https://github.com/samihsoylu/journal/releases/latest).

Extract the downloaded zip file and upload it to your web server.

**To install via composer (skip step 3):**
```
composer create-project samihsoylu/journal --no-dev
```

### 2. Edit .env file

Make a copy of the `.env.example` file and name it `.env` and fill in the settings.

If your domain url will be `example.com/my-journal` then set your base url to `BASE_URL = "/my-journal"`

### 3. Run composer

Run the following command to install the required composer libraries for your Journal application.

```bash
composer install --no-dev
```

### 4. Point your web server to the /public directory

In most PHP projects, files that should be exposed to the web are placed in a **public** folder. While files that should be hidden are left in a **private** folder. This helps security and  keeps the project better maintainable.

* You must tell your web server to load the public directory.

**For NGINX:**

If you are running nginx, you need to set a custom rewrite because the provided htaccess file won't work for you.

```bash
if (!-e $request_filename){
  rewrite ^(.+)$ /index.php?url=$1 break;
}
```

### 5. Run the database migration

```bash
vendor/bin/doctrine-migrations migrate --no-interaction
```

### 6. Create a new account

```bash
chmod +x bin/*

php bin/journalctl user:create
```
