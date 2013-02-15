# CampusBoard

A classifieds board for university students to buy, sell and give away textbooks, furniture, electronics and rides. Built with plain **PHP + MySQL + jQuery**, without a framework.

> Portfolio reference implementation of an early (2010) project, built using the stack of that era with current PHP language features (8.1+).

## Features

- Registration limited to `.edu` emails, bcrypt password hashing with transparent rehash
- Post, browse, search and filter listings by category, with pagination
- jQuery live search (debounced, cancels stale requests, keeps the URL shareable) over a JSON endpoint
- Owners can mark listings sold, relist or remove them; ownership is enforced in SQL
- CSRF tokens on every POST, output escaping, prepared statements, session fixation protection, open-redirect-safe login `next`
- MySQL schema with FULLTEXT and composite indexes

## Structure

```
public/index.php        front controller and router
src/                    Db, Auth, Csrf, Validator, ListingRepository, View
templates/              PHP views
public/assets/          jQuery app.js and CSS
schema.sql              MySQL schema and seed categories
tests/                  PHPUnit (repository tests run on in-memory SQLite)
```

## Run

```bash
docker compose up        # http://localhost:8000
# or locally
composer install
mysql -u root < schema.sql
composer serve
```

## Test

```bash
composer install
composer test
```

## License

MIT
