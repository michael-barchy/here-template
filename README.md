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

$here = new HereTemplate();
echo $here->render('templates/welcome.here.php');
```

### Options

HereTemplate class can be instantiated with the following options array:

* `dir`: default template directory (defaults to current directtory)
* `cache` : cache folder for compiled templates (defaults to cache/)

```php
<?php

$here = new HereTemplate(array(
    'dir' => 'templates',
    'cache' => sys_get_temp_dir()
));
echo $here->render('welcome.here.php');
```

## Templates

### Template formats

```php
<?php

$today = date('Y-m-d');

return <<<HTML
<h1>Today is {$today}</h1>
HTML;
```

```php
<h1>Today is {$today}</h1>
```

### Context

```php
<?php

use HereTemplate\HereTemplate;

$here = new HereTemplate();
echo $here->render('templates/welcome.here.php', array(
    'today' => date('d/m/Y')
));
```

### Protect your templates

Recommended: name your templates with a .here.php extension.

The `return` syntax is not mandatory but avoids execution outside engine context.
It also protects template source.

You can also create a `.htaccess` file in the `templates` folder to prevent direct access to template files.

## Extended HEREDOC syntax

HereTemplate acts as a compiler by extending HEREDOC syntax to add features such as `if` blocks, loops, etc.

### Conditions

```php
<?php

$d = intval(date('N'));

return <<<HTML
Today {$this->trueFalse(1 === $d, 'is', 'is not')} monday
HTML;
```

```
Today
    @if(1 === $d)
        is
    @else
        is not
    @endif
monday
```

### Loops

```php
<?php

$list = array('1', '2', '3');

return <<<HTML
<ul>{$this->forAll($list, 'value', '<li>%s</li>')}</ul>
HTML;
```

```html
<ul>
    @foreach(list as value)
        <li>%s</li>
    @endforeach
</ul>
```
