# Kirby Starterkit (Tailwind CSS + Alpine JS)

Personal starterkit for Kirby based projects, it includes Tailwind CSS and Alpine JS + some Kirby plugins.

## Requirements

-   Composer
-   NPM
-   PHP server (personally using Valet Laravel)

## Installation

1. Go to production folder

2. Clone repo

```
git repo https://github.com/az3ta/kirby-tailwind-alpine-starter YOUR-PROJECT
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
