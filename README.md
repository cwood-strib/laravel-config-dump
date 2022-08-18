# Laravel Config Dump 🚚

Dumps all the ENV keys that a Laravel codebase uses. This could probably be done with a clever sequence of `grep` and `uniq`, but here we are.


## Commands

### config

Parses the config files from a Laravel application. 

```
./lcd config ../my-laravel-app
```

Outputs categorized config names in Laravel dot path format, value, and type.

Types are one of:
`ENV_KEY` - A value derived from a call to the `env` function. Values are the key of the environmental value.
`LITERAL` - A literal value. 
`DYNAMIC` - The operation to determine this value cannnot be determined statically. Value is empty. 

Example:

```
logging.api_key | LOGGING_API_KEY | ENV_KEY
```

### env

Outputs all the environmental keys referenced in the application from analysis of `env` function calls.

```
./lcd config ../my-laravel-app
```


## Current Limitations 
- Only a subset of PHP expressions are supported for parsing in the config files. When something cannot be parsed, an error will be thrown.
- Currently, inspection into non-`env` function calls is not supported. These show up as `DYNAMIC`, even though they may, in theory, contain calls to `env` internally. 

## Install 

```sh
composer install
```

## Usage

```
./lcd path-to-php-project
```

## Example 

For an example, you can run the script against the `data` folder.

```
./lcd data
```
