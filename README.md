# skill-md

![Test Status](https://github.com/raphaelstolt/skill-md/workflows/test/badge.svg)
![Lint Status](https://github.com/raphaelstolt/skill-md/workflows/lint/badge.svg)
[![Version](http://img.shields.io/packagist/v/stolt/skill-md.svg?style=flat)](https://packagist.org/packages/stolt/skill-validator)
![Downloads](https://img.shields.io/packagist/dt/stolt/skill-md)
![PHP Version](https://img.shields.io/badge/php-8.2+-ff69b4.svg)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat)](https://github.com/php-pds/skeleton)
[![Lean dist package](https://img.shields.io/badge/lean-dist%20package-00ffb6.svg?style=flat)](https://github.com/raphaelstolt/lean-package-validator)

A PHP library to abstract a `SKILL.md` file.

## Installation
```bash
composer require stolt/skill-md
```

## Usage

### Creating a `SKILL.md` representation from an array

```php
 $skillMdData = [
    'name' => 'Code Review',
    'description' => 'Performs automated code reviews.',
    'version' => '1.0.0',
    'tags' => ['php', 'qa'],
];

$skillMd = SkillMd::fromArray($skillMdData);
```

### Creating a `SKILL.md` representation via class properties

```php
$skillMd = new SkillMd(
    name: 'Code Review',
    description: 'Performs automated code reviews.',
    ['version' => '1.0.0', 'language' => 'PHP', tags' => ['php', 'qa']]
);
```

### Accessing `SKILL.md` data

```php
$skillName = $skillMd->name();
$skilleDescription = $skillMd->description();
$skillVersion = $skillMd->version();
$skillTags = $skillMd->tags();
$skillLanguage = $skillMd->get('language');
```

### Running tests

```bash
composer test
```

### License

This library is licensed under the MIT license. Please see [LICENSE.md](LICENSE.md) for more details.

### Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more details.

### Contributing

Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) for more details.
