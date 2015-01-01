# phi

Simple map-phi-ng functions.

## Table of Contents

- [Config](#config)

## Config

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