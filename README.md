# tc-lib-testrest

*Please consider supporting this project by making a donation to <paypal@tecnick.com>*

* **category**    Library
* **package**     \Com\Tecnick\TestRest
* **author**      Nicola Asuni <info@tecnick.com>
* **copyright**   2015-2015 MediaSift Ltd. <http://datasift.com>
* **license**     The MIT License (MIT) - see [LICENSE](LICENSE)
* **link**        https://github.com/tecnickcom/tc-lib-testrest

## Status
* **MASTER**: [![Build Status](https://secure.travis-ci.org/tecnickcom/tc-lib-testrest.png?branch=master)](https://travis-ci.org/tecnickcom/tc-lib-testrest?branch=master)
[![Coverage Status](https://coveralls.io/repos/tecnickcom/tc-lib-testrest/badge.svg?branch=master&service=github)](https://coveralls.io/github/tecnickcom/tc-lib-testrest?branch=master)
* **DEVELOP**: [![Build Status](https://secure.travis-ci.org/tecnickcom/tc-lib-testrest.png?branch=develop)](https://travis-ci.org/tecnickcom/tc-lib-testrest?branch=develop)
[![Coverage Status](https://coveralls.io/repos/tecnickcom/tc-lib-testrest/badge.svg?branch=develop&service=github)](https://coveralls.io/github/tecnickcom/tc-lib-testrest?branch=develop)


## Description

This library contains utility classes to test end-to-end RESTful services using Gherkin language.

The tests are based on [Behat](http://behat.org).


## Installation

This project requires PHP 5.4.0+ to use the PHP built-in web server.

* Create a composer.json in your projects root-directory and include this project:

```json
{
    "require-dev": {
        "tecnickcom/tc-lib-testrest": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tecnickcom/tc-lib-testrest.git"
        }
    ]
}
```
* Create a behat.yml file in the root directory of your project like the one in test/behat.yml and check the internal comments and options.
* Create a test/features folder in your project like the one in test/features and write your own ".feature" files like the provided example.
* Create (or update) a makefile like the one in this project which contains the "btest" target. This target starts the PHP built-in server and execute the Behat tests.


## Development - getting started

First, you need to install all dependencies (you'll need [composer](https://getcomposer.org/)):
```bash
$ cd /tmp && curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

The following command will download all the composer dependencies required for development and testing:
```bash
make build_dev
```

### Running Tests

The internal unit tests includes a database testing, so you need to create the following MySQL database with the right privileges:

```sql
CREATE DATABASE IF NOT EXISTS testrest_test;
GRANT ALL ON testrest_test.* TO 'testrest'@'%' IDENTIFIED BY 'testrest';
FLUSH PRIVILEGES;
```

To execute all the tests you can now run `make qa_all`.

Please issue the command `make help` to see all available options and execute individual tests.

### Coding standards

This project follows the PSR2 coding standard. To see any errors in your code, you can use the `make phpcs` command.
We also use a tool to detect any code smells. To run it, use `make phpmd`.

Before submitting a Pull Request, please execute the `make qa_all` to be sure that no errors where introduced.
Additionally, please check the target/coverage/index.html report to be sure that every line of code is covered by a unit test.
If you add any new gherkin language feature please also add an example in test/features.


## Developer(s) Contact

* Nicola Asuni <info@tecnick.com>
