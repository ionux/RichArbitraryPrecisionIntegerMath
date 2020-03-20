# Rich Arbitrary Precision Integer Math for PHP

[![Build Status](https://travis-ci.org/ionux/RichArbitraryPrecisionIntegerMath.svg?branch=master)](https://travis-ci.org/ionux/RichArbitraryPrecisionIntegerMath)

## Description

This rich arbitrary precision integer mathematics (RAPIM) library for PHP is a project which aims to provide a completely self-contained solution for PHP projects that cannot install the GMP or BC math extensions.  It can, of course, take advantage of those higher-performing math extensions but the library will fall back on pure PHP implementations of the math functions if they are not present.

RAPIM is a work in progress and should be considered alpha quality software.  I would appreciate and welcome all feedback, especially if you have experience in implementing math algorithms.  But all programming, debugging and test writing help is welcome!

## Installing

Installation of this project is very easy using composer:

```php
php composer.phar require ionux/rapim:1.0.0
```

If you have git installed, you can clone the repository:

```sh
git clone https://github.com/ionux/RichArbitraryPrecisionIntegerMath.git
```

Or you can install manually by downloading the zip file and extracting the contents into your project's source directory.

## Usage

Integrating these classes with your project is very simple.  For example, after including the necessary class files (or using an autoloader), to perform calculations:

```php
$math = new \RAPIM\Math;

$result = $math->add($a, $b);
```

The result will be returned as a string value:

```sh
87184393310133944941037516098150368062480930902710059558578327529121455801724207495015120248109543168498739812179
```

## Found a bug?

Let me know! Send a pull request or a patch. Questions? Ask! I will respond to all filed issues.

**Support:**

* [GitHub Issues](https://github.com/ionux/RichArbitraryPrecisionIntegerMath/issues)
  * Open an issue if you are having issues with this library

## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/ionux/RichArbitraryPrecisionIntegerMath/issues) and tell me about it.

If you *are* a developer wanting contribute an enhancement, bugfix or other patch to this project, please fork this repository and submit a pull request detailing your changes. I review all PRs!

This open source project is released under the [MIT license](http://opensource.org/licenses/MIT) which means if you would like to use this project's code in your own project you are free to do so.  Speaking of, if you have used this math library in a cool new project I would like to hear about it!

## License

```
  Copyright (c) 2014-2020 Rich Morgan, rich@richmorgan.me

  The MIT License (MIT)

  Permission is hereby granted, free of charge, to any person obtaining a copy of
  this software and associated documentation files (the "Software"), to deal in
  the Software without restriction, including without limitation the rights to
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
  the Software, and to permit persons to whom the Software is furnished to do so,
  subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
