# PHI API

PHP hyper ultra simple and mega fast (just 4 functions!!!) route => callback mapper

# Handle errors

```php
# map handler against error codes, first argument is the error code
\phi\map(404, function ($code) {});
\phi\map([400, 401, 403, 404], function ($code) {});
```