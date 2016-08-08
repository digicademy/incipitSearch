###Command Line Scripts Read Me

####Usage
Just call the scripts from the command line like this:

```
php command_line_scripts/scriptname.php
```

####Important
The scripts must be called from the same working directory as index.php.
Otherwise the paths for config and autoload will not match and you get an ugly error.

For example: If index.php lies on the root directory call the script from the root directory with 

```
php command_line_scripts/scriptname.php
```
If index.php lies in public call the scripts like this:
```
cd public
php ../command_line_scripts/scriptname.php
```