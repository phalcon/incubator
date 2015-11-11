# Phalcon\Cli\Input

## Interfaces

### ParameterInterface

This interface is meant to represent an abstract command line `parameter`.
This interface must be used as a base interface for any parameters - `options` and `arguments`
(so-called `operands`).

This interface includes properties for each of the following:

- Parameter name
- Parameter description
- Parameter default value

### OptionInterface

This interface is meant to represent a command line `option` according to [IEEE Std 1003.1, 2013 Edition][1]
and to provide methods for most common operations. Additional functionality for working with options can be provided
on top of the interface (`ParameterInterface`) or externally.

This interface includes properties for each of the following:

- Option value type (unacceptable, optional, required, array)

### ArgumentInterface

This interface is meant to represent a command line `argument` according to [IEEE Std 1003.1, 2013 Edition][1]
and to provide methods for most common operations. The ArgumentInterface uses a convention stating that the `argument`
is a synonym of the `operand`. Additional functionality for working with arguments can be provided on top of the
interface (`ParameterInterface`) or externally.

This interface includes properties for each of the following:

- Argument type (optional, required, array)

[1]: http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap12.html
