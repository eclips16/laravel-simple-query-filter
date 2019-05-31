# Laravel Simple Query Filters
A simple PHP Eloquent extension for universal filters.

Heavily inspired by https://github.com/AlexanderTersky/eloquent-query-filter but with a better support for relation

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

Some real life example:

```php
/api/users/?filter[name:like]=john
/api/users/?filter[age:>]=18&filter[age:<]=25
/api/users/?filter[gender:<>]=male
```

### Include the trait in your model

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
