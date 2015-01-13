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
# map handler against error codes, first argument is the error code
\phi\map(404, function ($code) {});
\phi\map([400, 401, 403, 404], function ($code) {});
```

## Route Parameters

## Dispatch
