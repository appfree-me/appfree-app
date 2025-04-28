# Best practices


## Code style

Every PHP source file has to include the following directive as a first line: 

`declare(strict_types=1);`

Without this line, type annotations in PHP source files will not be processed correctly.



### .env files

.env files also get sourced directly by bash scripts at the moment. Environment variables only support strings.
If you want to represent a boolean value, please only use the strings "false" and "true" as values so these can be consistently interpreted by bash.
For usage inside of the Laravel app, some more types and conventions are documented under https://laravel.com/docs/12.x/configuration#environment-variable-types


## Commit rules

- Before committing, please make sure that you...
  - execute `composer lint:fix` to adjust code style and formatting to the project
  - execute `composer lint` to perform static checks. 
  - execute `composer test` to execute the tests. These also include some code quality tests, e. g. type safety/rules for how DTOs. 

Code may only be integrated when all checks and tests are passing. 

## Tests

New appfree modules should at the minimum come with an integration test covering a sample phone workflow. 

This can be used for the watchdog process in production.

### Tests and the Database

appfree uses PEST PHP Tests. Tests can read and write from the currently configured database, but without committing their changes. This allows for a powerful way to write complex integration tests which require multiple components interacting. 

