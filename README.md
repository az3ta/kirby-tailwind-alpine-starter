# Kirby Starterkit

[![Release](https://img.shields.io/github/v/release/az3ta/kirby-tailwind-alpine-starter)](https://img.shields.io/github/v/release/az3ta/kirby-tailwind-alpine-starter/releases)

Personal starterkit for Kirby based projects, it includes Tailwind CSS and Alpine JS + some Kirby plugins.

## Requirements

-   (Composer)[https://getcomposer.org]
-   (NPM)[https://www.npmjs.com]
-   PHP server (e.g. (Valet Laravel)[https://laravel.com/docs/9.x/valet])

## Installation

1. Go to working folder

2. Clone repo

```
git clone https://github.com/az3ta/kirby-tailwind-alpine-starter YOUR-PROJECT
cd YOUR-PROJECT
```

3. Install Kirby and dependencies

```
composer install
composer update getkirby/cms
composer update
```

3. Install NPM dependencies

```
npm install
```

4. Secure dev address

```
valet secure
```

## Development

```
npm run watch
```

## Production

```
npm run build
```
