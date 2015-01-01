
## Config function

```php
function config() {
	retrun \phi\config([], include __DIR__ . '/config.php') 
}
```

Reading and update values

```php
config()->value = 'save'
echo config()->value; // will print save 
```