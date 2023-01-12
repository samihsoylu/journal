# Journal
Journal is a privacy first, self-hosted digital log book. It is designed to be accessible anywhere you go, allowing you to organize your thoughts, feelings and opinions in one place. Your Journals are stored with AES 256 encryption using your own password as the encryption key, resulting in a protected and inaccessible log book from outsiders.

<img src="https://samihsoylu.nl/downloads/dashboard-journalv1.2.3.png">

## Feature highlights
* Mobile friendly
* Advanced content editor (supports image upload)
* Organise entries using categories
* Create predefined sets of templates for entries
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
Visit: `http://localhost:8080/` Username: `demouser` Password: `demopass`

## Requirements
- A Linux-based server with shell access
- PHP 7.4 or later.
- MySQL 5.6+

### PHP Extensions

The listed extensions are usually installed and enabled by default in most PHP 7 installations

- [json](https://www.php.net/manual/en/book.json.php)
- [pdo](https://www.php.net/manual/en/book.pdo.php)
- [openssl](https://www.php.net/manual/en/book.openssl.php)
- [tokenizer](https://www.php.net/manual/en/book.tokenizer.php)
- [mbstring](https://www.php.net/manual/en/book.mbstring.php)
- [ctype](https://www.php.net/manual/en/book.ctype)
- [pcre](https://www.php.net/manual/en/book.pcre)
- [session](https://www.php.net/manual/en/book.session)

## Documentation

* [Installation](https://samihsoylu.notion.site/Installation-fb156297be1f421c8540a41fe34314ec)
* [Upgrading](https://samihsoylu.notion.site/Upgrading-04fcbde744c244bcacad577604c43b41)
