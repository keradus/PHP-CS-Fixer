UPGRADE GUIDE FROM 2.x to 3.0
=============================

This is guide for upgrade from version 2.x to 3.0 for using the CLI tool.


CLI options
-----------

| 2.x             | 3.0             | Description                                                                    | Note                                   |
| --------------- | --------------- | ------------------------------------------------------------------------------ | -------------------------------------- |
| --show-progress | --show-progress | Type of progress indicator                                                     | Allowed values were modified:          |
|                 |                 |                                                                                | `run-in` and `estimating` was removed, |
|                 |                 |                                                                                | `estimating-max` was removed to `dots` |

Changes to rules
----------------

Rule | Option | Old value | New value
---- | ---- | ---- | ----
`method_argument_space` | `ensure_fully_multiline` | `false` | `true`
`non_printable_character` | `use_escape_sequences_in_strings` | `false` | `true`
`php_unit_dedicate_assert` | `target` | `5.0` | `newest`
`phpdoc_align` | `tags` | `['param', 'return', 'throws', 'type', 'var']` | `['method', 'param', 'property', 'return', 'throws', 'type', 'var']`
