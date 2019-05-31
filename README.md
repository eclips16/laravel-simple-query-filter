# Laravel Simple Query Filters
A simple PHP Eloquent extension for universal filters.

## Installation

```
$ composer require exeko/laravel-simple-query-filter
```

```json
{
    "require": {
        "exeko/laravel-simple-query-filter": "^1.0"
    }
}
```

## Usage

Our request must look like

```php
/filter[column_name:operator]=something
```

### Inclusion the trait to your model

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Exeko\QueryFilter\Filter;

class User extends Model
{
    use Filter;
}
```

### Controller
```php
$users=User::filter($request->input('filter'))->get();
```
