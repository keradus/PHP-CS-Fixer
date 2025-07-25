========================================
Rule ``no_null_property_initialization``
========================================

Properties MUST not be explicitly initialized with ``null`` except when they
have a type declaration (PHP 7.4).

Examples
--------

Example #1
~~~~~~~~~~

.. code-block:: diff

   --- Original
   +++ New
    <?php
    class Foo {
   -    public $bar = null;
   +    public $bar;
        public ?string $baz = null;
        public ?string $baux;
    }

Example #2
~~~~~~~~~~

.. code-block:: diff

   --- Original
   +++ New
    <?php
    class Foo {
   -    public static $foo = null;
   +    public static $foo;
    }

Rule sets
---------

The rule is part of the following rule sets:

- `@PhpCsFixer <./../../ruleSets/PhpCsFixer.rst>`_
- `@Symfony <./../../ruleSets/Symfony.rst>`_

References
----------

- Fixer class: `PhpCsFixer\\Fixer\\ClassNotation\\NoNullPropertyInitializationFixer <./../../../src/Fixer/ClassNotation/NoNullPropertyInitializationFixer.php>`_
- Test class: `PhpCsFixer\\Tests\\Fixer\\ClassNotation\\NoNullPropertyInitializationFixerTest <./../../../tests/Fixer/ClassNotation/NoNullPropertyInitializationFixerTest.php>`_

The test class defines officially supported behaviour. Each test case is a part of our backward compatibility promise.
