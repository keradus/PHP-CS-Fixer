=======================================
Rule set ``@PHPUnit100Migration:risky``
=======================================

Rules to improve tests code for PHPUnit 10.0 compatibility.

Warning
-------

This set contains rules that are risky
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using this rule set may lead to changes in your code's logic and behaviour. Use it with caution and review changes before incorporating them into your code base.

Rules
-----

- `@PHPUnit91Migration:risky <./PHPUnit91MigrationRisky.rst>`_
- `php_unit_data_provider_static <./../rules/php_unit/php_unit_data_provider_static.rst>`_ with config:

  ``['force' => true]``

