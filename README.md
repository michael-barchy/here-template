# HereTemplate

HereTemplate is a template engine and compiler that extends the HEREDOC syntax.

## Install

The engine can be installed using composer or just by loading the class.

### Using composer

```shell
composer config repositories.here-template '{"type": "vcs", "no-api": true, "url": "https://github.com/michael-barchy/here-template"}'
composer require michael-barchy/here-template:dev-main
```

### Without composer

```shell
git submodule add https://github.com/michael-barchy/here-template
```

```shell
git submodule update --remote
```

## Usage

### Loading using composer

```php
<?php

require_once('vendor/autoload.php');
```

### Loading without composer
```php
<?php

require_once('here-template/HereTemplate.php');
```

## Using the engine

```php
<?php

use HereTemplate\HereTemplate;


