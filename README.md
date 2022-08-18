# Laravel Config Dump 🚚

Assists analysis of Laravel application configuration

## Commands

### config

Parses config files from a Laravel application and outputs categorized config names in the format `Laravel dot path format name | value | type`.

```
./lcd config ../my-laravel-app/config
```

Types are one of:
- `ENV_KEY` - A value derived from a call to the `env` function. Values are the name of the environmental variable.
- `LITERAL` - A literal value. 
- `DYNAMIC` - The operation to determine this value cannnot be determined statically. Value is empty. 

Example:

```
logging.api_key | LOGGING_API_KEY | ENV_KEY
```

### env

Outputs all the environmental keys referenced in the application from analysis of `env` function calls.

```
./lcd env ../my-laravel-app
```


## Current Limitations 
- Only a subset of PHP expressions are supported for parsing in the config files. When something cannot be parsed, an error will be thrown.
- Currently, inspection into non-`env` function calls is not supported. These show up as `DYNAMIC`, even though they may, in theory, contain calls to `env` internally.   

## Install 

```sh
composer install
```

## Example 

For an example, you can run the script against the `data` folder.

```
./lcd env data
```
