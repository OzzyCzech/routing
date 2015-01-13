# PHI API

PHP hyper ultra simple and mega fast (just 4 functions!!!) route => callback mapper

## Handlers

```php
// map against all types of requests
\phi\map('/', function() {});

// map handler against method(s) + route
\phi\map('GET', '/', function() {});
\phi\map(['GET', 'POST'], '/account/new', function() {});

// map handler against a route(s)
\phi\map(['/kontakt', '/contact'], function () {});

// map handler against everything
\phi\map(function () {});
```

## Error Handlers

```php
// map handler against error codes, first argument is the error code
\phi\map(404, function ($code) {});
\phi\map([400, 401, 403, 404], function ($code) {});
\phi\map(500, function ($code) {});
```

## Route Parameters

```php
// if you have a symbols in any route
/phi/map('GET', '/users/{id}', function ($params) {
  $id = $params['id'];
});

// attach regex rules to your route 
/phi/map('GET', '/users/{id [0-9]+}', function ($params) {
  $id = $params['id'];
});

```

## Dispatch

```php
// Application entry point 
\phi\dispatch();

// or with params
\phi\map('/', function($config) {});
\phi\dispatch($config = new Config);
```
