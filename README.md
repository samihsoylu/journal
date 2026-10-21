# Journal
Journal is a privacy first, self-hosted digital log book. It is designed to be accessible anywhere you go, allowing you to organize your thoughts, feelings and opinions in one place. Your Journals are stored with AES 256 encryption using your own password as the encryption key, resulting in a protected and inaccessible log book from outsiders.

<img src="https://samihsoylu.nl/downloads/dashboard-journalv1.2.3.png">

## Feature highlights
* Mobile friendly
* Advanced content editor (supports image upload)
* Organise entries using categories
* Create predefined sets of templates for entries
* Choose entry dates and optional template title patterns
* Optional authenticator login with one-use recovery codes
* Install Journal from a supporting browser (online access required)
* Entries, Templates and Images have AES-256 Encryption
* Order your favorite categories to appear on the top
* Quickly add entries by enabling the quick-add widget
* Advanced search
* Create accounts for users
* Export entries

## Getting started

* You can use [our Hosted version](https://journalapp.nl). This is the simplest way to use Journal.
* You can [host it yourself](https://samihsoylu.notion.site/Installation-fb156297be1f421c8540a41fe34314ec)
* You can join our [Discord](https://discord.gg/bfkMjU5teE) if you have questions

## Try it out using Docker
```bash
curl -o docker-compose.yml https://raw.githubusercontent.com/samihsoylu/journal/master/docker-compose.yml

docker compose up -d
```
Visit: `http://localhost:8080/` Username: `demouser` Password: `journal-demo-password`

The quick start binds to localhost and creates demo credentials for local evaluation.
Set your own `USERNAME` and `PASSWORD` for an installation you plan to keep.

New `v*` version tags publish the Docker image and update `latest`; merges only
run checks. If you already have the image, run `docker compose pull journal`
before `docker compose up -d` to fetch the latest published version. For existing
installations, follow the [upgrade instructions](docs/upgrading.md) first.

## Requirements
- A Linux-based server with shell access
- PHP 8.5
- MySQL or MariaDB (checks cover MySQL 5.7/8.0 and MariaDB 10.4/10.11; use maintained versions for new installs)

### PHP Extensions

The listed extensions are commonly available in standard PHP installations.

- [json](https://www.php.net/manual/en/book.json.php)
- [pdo](https://www.php.net/manual/en/book.pdo.php)
- [openssl](https://www.php.net/manual/en/book.openssl.php)
- [tokenizer](https://www.php.net/manual/en/book.tokenizer.php)
- [mbstring](https://www.php.net/manual/en/book.mbstring.php)
- [ctype](https://www.php.net/manual/en/book.ctype)
- [pcre](https://www.php.net/manual/en/book.pcre)
- [session](https://www.php.net/manual/en/book.session)
- [zip](https://www.php.net/manual/en/book.zip.php) (entry exports)

## Development

```bash
composer install
./bin-dev/start
```

The development script starts MariaDB in Docker, prepares the development and
test databases, runs migrations, and serves the application with host PHP at
<http://127.0.0.1:8080>.

Common checks:

```bash
./vendor/bin/phpunit
./vendor/bin/phpstan analyse -c phpstan.neon
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Documentation

* [Architecture](docs/architecture.md)
* [Testing](docs/testing.md)
* [Development environment](bin-dev/README.md)
* [Upgrading an existing v1 installation](docs/upgrading.md)
* [Installation](https://samihsoylu.notion.site/Installation-fb156297be1f421c8540a41fe34314ec)
* [Upgrading](https://samihsoylu.notion.site/Upgrading-04fcbde744c244bcacad577604c43b41)
