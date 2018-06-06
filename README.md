# PHPUnitHelperAssertionsTrait (v0.2.0)

Additional helpful phpunit assertions. To use in your PHPUnit tests, [add using the `use` statement](http://php.net/manual/en/language.oop5.traits.php).

Example with `WP_UnitTestCase`:

```php
<?php
namespace Jtsternberg\Example\Tests;

// You can require directly, or add to your `composer.json` "require-dev" array.
// require_once __DIR__ . '/PHPUnitHelperAssertionsTrait/PHPUnitHelperAssertionsTrait.php';

/**
 * Tests Base.
 *
 * @since   0.1.0
 * @package JtsternbergExampleTests
 */
abstract class Base extends \WP_UnitTestCase {
	use \PHPUnitHelperAssertionsTrait;

	protected static function ns( $append = '' ) {
		return "AwesomeMotive\\Example\\$append";
	}
}


/**
 * Test the core of my plugin.
 *
 * @since   0.1.0
 * @package JtsternbergExampleTests
 */
class testCore extends Base {

	public function test_core_loaded() {
		$this->assertTrue( defined( 'JTSTERNBERG_EXAMPLE_VERSION' ) );
	}

	public function test_core_loaded() {
		$expected = '<section>
			<h1>Hellow World</h1>
		</section>'
		$this->assertHTMLstringsAreEqual( $expected, func() );
	}

}

```