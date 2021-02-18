# Sphido / Routing

Ultra simple and fast (only 5 functions!!!) `route => callback` mapper

## Handlers

```php
// map against all types of requests
/route/map('/', function() {});

// map handler against method(s) + route
/route/map('GET', '/', function() {});
/route/map(['GET', 'POST'], '/account/new', function() {});

// map handler against a route(s)
/route/map(['/kontakt', '/contact'], function () {});

// map handler against everything
/route/map(function () {});
```

## Error Handlers

```php
// map handler against error codes, first argument is the error code
/route/map(404, function ($code) {});
/route/map([400, 401, 403, 404], function ($code) {});
/route/map(500, function ($code) {});
```

## Route Parameters

```php
// if you have a symbols in any route
/route/map('GET', '/users/<id>', function ($params) {
  $id = $params['id'];
});

// attach regex rules to your route 
/route/map('GET', '/users/<id:[0-9]+>', function ($params) {
  $id = $params['id'];
});

// language selection in route
/route/map('GET', '/<lang:[a-z]{2}>/page', function ($params) {
  $lang = $params['lang'];
});

```

## Dispatch

```php
/app/dispatch(); // process request and dispatch results
```

or with some params

```php
/route/map('/', function($config) {});
/app/dispatch($config = new Config);
```
or as your `app` object

```php
class app {
  function __invoke($method, $path, $me) {
    // handle $path/$method as you need
  }
  function __destruct() {
    /app/dispatch($this);
  }
}
/route/map(new app);
```


## Possible changes

- default values `<param=cz:[a-z]{2}>` ???
- case sensitivity vs. insensitivity ??? currently is case sensitive
- optional parametter `(<param>)?` ???
- optional strings `<name>(.html)?` ????
