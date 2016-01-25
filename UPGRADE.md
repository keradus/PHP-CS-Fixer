UPGRADE GUIDE FROM 1.x to 2.0
=============================

This is guide for upgrade from version 1.x to 2.0 for using the CLI tool.

Config file
-----------
From now you can create new configuration file: `.php_cs.dist`. This file is prioritized over `.php_cs` configuration file. It is recommended to use first one and add second one to `.gitignore` to allow your contributors to have theirs own configuration file.

Config and Finder classes
-------------------------
All off `Symfony\CS\Config\*` and `Symfony\CS\Finder\*` classes have been removed, instead use `Symfony\CS\Config` and `Symfony\CS\Finder`.

For that reason you can not set config class by `--config` CLI argument, from now it is used to set configuration file. Therefor the `--config-file` CLI argument is no longer available.

Rules and sets
--------------
To configure which fixers should be used one must now set rules and sets instead of fixers and level. This affects both configuration file and CLI arguments.

The term of risky rules was introduced. Risky rule is a rule that may change the meaning of code (like `strict` rule, which will change `==` into `===`). No risky rules are run by default. One need to explicitly permit risky rules to run them.

Changes to rules
----------------
Rules that have been removed:

Name | Reason
---- | ------
phpdoc_var_to_type | use phpdoc_type_to_var instead, which follows PSR-5

Rules that have been renamed:

Old name | New name | Additional info
-------- | -------- | ---------------
array_element_no_space_before_comma            | no_whitespace_before_comma_in_array
array_element_white_space_after_comma          | whitespace_after_comma_in_array
blankline_after_open_tag                       | blank_line_after_opening_tag
double_arrow_multiline_whitespaces             | double_arrow_no_multiline_whitespace
duplicate_semicolon                            | no_duplicate_semicolons
empty_return                                   | simplified_null_return
eof_ending                                     | single_blank_line_at_eof
extra_empty_lines                              | no_extra_consecutive_blank_lines                  | new configuration options have been added
function_call_space                            | no_spaces_after_function_name
indentation                                    | no_tab_indentation
join_function                                  | no_alias_functions                                | new one fixes more aliases
line_after_namespace                           | blank_line_after_namespace
linefeed                                       | unix_line_endings
list_commas                                    | no_trailing_comma_in_list_call
logical_not_operators_with_spaces              | not_operators_with_space
logical_not_operators_with_successor_space     | not_operator_with_successor_space
method_argument_default_value                  | no_unreachable_default_argument_value
multiline_array_trailing_comma                 | trailing_comma_in_multiline_array
multiline_spaces_before_semicolon              | no_multiline_whitespace_before_semicolons
multiple_use                                   | single_import_per_statement
namespace_no_leading_whitespace                | no_leading_namespace_whitespace
newline_after_open_tag                         | linebreak_after_opening_tag
no_empty_lines_after_phpdocs                   | no_blank_lines_after_phpdoc
object_operator                                | object_operator_without_whitespace
operators_spaces                               | binary_operator_spaces
ordered_use                                    | ordered_imports
parenthesis                                    | no_spaces_inside_parenthesis
php4_constructor                               | no_php4_constructor
php_closing_tag                                | no_closing_tag
phpdoc_params                                  | phpdoc_align
phpdoc_short_description                       | phpdoc_summary
remove_leading_slash_use                       | no_leading_import_slash
remove_lines_between_uses                      | no_blank_lines_between_uses
return                                         | blank_line_before_return
short_bool_cast                                | no_short_bool_cast
short_echo_tag                                 | no_short_echo_tag
short_tag                                      | full_opening_tag
single_array_no_trailing_comma                 | no_trailing_comma_in_singleline_array
spaces_after_semicolon                         | space_after_semicolon
spaces_before_semicolon                        | no_singleline_whitespace_before_semicolons
standardize_not_equal                          | standardize_not_equals
ternary_spaces                                 | ternary_operator_spaces
trailing_spaces                                | no_trailing_whitespace
unary_operators_spaces                         | unary_operator_spaces
unneeded_control_parentheses                   | no_unneeded_control_parentheses
unused_use                                     | no_unused_imports
visibility                                     | visibility_required

Rules that have been added:

Name              |
----------------- |
method_separation |