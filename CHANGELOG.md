0.3.1 / 2020-07-07

* Fixed "Fatal error: Declaration of PHPUnitHelperAssertionsTrait::assertSame($expected, $actual, $message = '') must be compatible with PHPUnit\Framework\Assert::assertSame($expected, $actual, string $message = ''):"

0.3.0 / 2018-08-16
==================

* Add `readProperty` method to read inaccesible properties, to complement `invokeMethod`
* Add `assertSameSortedArray` method, which asserts that 2 arrays are the same (after sorting)
* `assertHTMLstringsAreEqual` and `assertStringsAreEqual` methods now both take a message parameter, as they should

v0.2.0 / 2018-06-06
===================

  * Add `assertArrayKeysExist` assertion method
  * Allow passing in additional message to `assertSameArray`

v0.1.1 / 2018-04-04
===================

  * Update composer autoload-dev file

v0.1.0 / 2018-04-04
===================

  * Begin!
