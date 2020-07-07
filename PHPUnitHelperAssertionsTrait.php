<?php
/**
 * Helpful Assertions for PHPUnit.
 *
 * @since   0.1.0
 * @version 0.3.1
 * @package PHPUnitHelperAssertions
 */
trait PHPUnitHelperAssertionsTrait {

	/**
	 * How many characters to show before the first difference in string comparisons.
	 *
	 * @since  0.1.0
	 *
	 * @var integer
	 */
	protected static $compareStringsCharsBefore = 15;

	/**
	 * How many characters to show after the first difference in string comparisons.
	 *
	 * @since  0.1.0
	 *
	 * @var integer
	 */
	protected static $compareStringsCharsAfter   = 75;

	/**
	 * The indicator to show the first difference in string comparisons.
	 *
	 * @since  0.1.0
	 *
	 * @var integer
	 */
	protected static $compareStringsPointer = '| ----> |';

	/**
	 * Asserts whether two html strings are equal after normalizing whitespace, etc.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $expected_string The expected result..
	 * @param  string  $string_to_test  The string to compare.
	 * @param  string|false $message    Optional additional message on failure.
	 */
	public function assertHTMLstringsAreEqual( $expected_string, $string_to_test, $message = false ) {
		$expected_string = $this->normalizeString( $expected_string );
		$string_to_test = $this->normalizeString( $string_to_test );

		$this->assertStringsAreEqual( $expected_string, $string_to_test, $message );
	}

	/**
	 * Asserts whether two strings are equal and output helpful message if not.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $expected_string The expected result..
	 * @param  string  $string_to_test  The string to compare.
	 * @param  string|false $message    Optional additional message on failure.
	 */
	public function assertStringsAreEqual( $expected_string, $string_to_test, $message = false ) {
		$compare = $this->compareStrings( $expected_string, $string_to_test );
		$msg = ! empty( $compare ) ? $compare : null;

		if ( false !== $message ) {
			$msg = null === $msg
				? $message
				: $msg . "\n\n" . $message;
		}

		$this->assertEquals( $expected_string, $string_to_test, $msg );
	}

	/**
	 * Asserts that 2 arrays are the same (after sorting) or outputs helpful message showing missing values.
	 *
	 * @since  0.3.0
	 *
	 * @param array        $expected_arr The expected result.
	 * @param array        $test_arr     The array to compare.
	 * @param string|false $message      Optional additional message on failure.
	 */
	public function assertSameSortedArray( $expected_arr, $test_arr, $message = false ) {
		ksort( $expected_arr );
		ksort( $test_arr );
		self::assertSameArray( $expected_arr, $test_arr, $message );
	}

	/**
	 * Asserts that 2 arrays are the same or outputs helpful message showing missing values.
	 *
	 * @since  0.1.0
	 *
	 * @param array        $expected_arr The expected result.
	 * @param array        $test_arr     The array to compare.
	 * @param string|false $message      Optional additional message on failure.
	 */
	public function assertSameArray( $expected_arr, $test_arr, $message = false ) {
		$msg = false;

		if ( $expected_arr !== $test_arr ) {
			// A more helpful fail message.
			$diff = self::diffArrays( $expected_arr, $test_arr );
			$msg = "Failed asserting that arrays are the same. More info:\n\n";
			if ( empty( $diff['removed'] ) && empty( $diff['added'] ) ) {
				$msg .= self::compareArraysAsStrings( $expected_arr, $test_arr );
			} else {
				$msg .= sprintf( "Should not have: %s\nMissing: %s", print_r( $diff['removed'], true ), print_r( $diff['added'], true ) );
			}

			$message = $message ? ( $msg . "\n\n" . $message ) : $msg;
		}

		$this->assertSame(
			$expected_arr,
			$test_arr,
			$message
		);
	}

	/**
	 * Asserts that array has all the given keys.
	 *
	 * @since 0.2.0
	 *
	 * @param array $keys  The array of keys to check.
	 * @param array $array The array to compare.
	 * @param string|false $message      Optional additional message on failure.
	 */
	public function assertArrayKeysExist( $keys, $array, $message = false ) {
		$expected = array_flip( $keys );
		$this->assertSameArray(
			$expected,
			array_intersect_key( $expected, $array ),
			$message ? $message : self::varExport( $array )
		);
	}


	/**
	 * Asserts that two variables have the same type and value.
	 * Used on objects, it asserts that two variables reference
	 * the same object.
	 *
	 * Polyfill for older versions of PHPUnit
	 *
	 * @param mixed  $expected
	 * @param mixed  $actual
	 * @param string $message
	 */
	public static function assertSame( $expected, $actual, string $message = '' ) : void {
		try {
			parent::assertSame( $expected, $actual, $message );
		} catch ( \Exception $e ) {
			if ( $message ) {
				$class = get_class( $e );
				$e = new $class( $message, $e->getComparisonFailure() );
			}

			throw $e;
		}
	}

	/**
	 * Gets a diff for two arrays.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $old_array
	 * @param  array $new_array
	 *
	 * @return array Diff array with "removed" and "added" diffs. A value of 0 means no change.
	 */
	protected static function diffArrays( $old_array, $new_array ) {
		return array(
			'removed' => self::diffAssocArrayRecursive( $old_array, $new_array ),
			'added'   => self::diffAssocArrayRecursive( $new_array, $old_array ),
		);
	}

	/**
	 * Recursively diff associative arrays.
	 * @link  https://www.codeproject.com/Questions/780780/PHP-Finding-differences-in-two-multidimensional-ar
	 *
	 * @since  0.1.0
	 *
	 * @param  array $array1
	 * @param  array $array2
	 *
	 * @return array|int Diff or 0
	 */
	protected static function diffAssocArrayRecursive( $array1, $array2 ) {
		foreach ( $array1 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! isset( $array2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} elseif ( ! is_array( $array2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} else {
					$new_diff = self::diffAssocArrayRecursive( $value, $array2[ $key ] );
					if ( false != $new_diff ) {
						$difference[ $key ] = $new_diff;
					}
				}
			} elseif ( ! isset( $array2[ $key ] ) || $array2[ $key ] != $value ) {
				$difference[ $key ] = $value;
			}
		}

		return isset( $difference ) ? $difference : 0;
	}

	/**
	 * Compare 2 arrays by converting to a serialized string first.
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $compare1     First array
	 * @param  array  $compare2     Second array
	 * @param  string $origLabel    "Expected" label
	 * @param  string $compareLabel "Actual" label
	 *
	 * @return string                Comparison result.
	 */
	protected static function compareArraysAsStrings( $compare1, $compare2, $origLabel = 'Expected', $compareLabel = 'Actual'  ) {
		return self::compareStrings(
			maybe_serialize( $compare1 ),
			maybe_serialize( $compare2 ),
			$origLabel,
			$compareLabel
		);
	}

	/**
	 * Get result of comparing 2 strings.
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $origString   Original string
	 * @param  array  $newString    String to compare.
	 * @param  string $origLabel    "Expected" label
	 * @param  string $compareLabel "Actual" label
	 *
	 * @return string                Comparison result.
	 */
	public static function compareStrings( $origString, $newString, $origLabel = 'Expected', $compareLabel = 'Actual' ) {
		$origLength = strlen( $origString );
		$newLength  = strlen( $newString );
		$compare    = strcmp( $origString, $newString );

		if ( 0 === $compare ) {
			return 0;
		}

		$labelSpacer   = str_repeat( ' ', abs( strlen( $compareLabel ) - strlen( $origLabel ) ) );
		$compareSpacer = $origSpacer = '';

		if ( strlen( $compareLabel ) > strlen( $origLabel ) ) {
			$origSpacer = $labelSpacer;
		} elseif ( strlen( $compareLabel ) < strlen( $origLabel ) ) {
			$compareSpacer = $labelSpacer;
		}

		$compare = strspn( $origString ^ $newString, "\0" );
		$start   = ( $compare - self::$compareStringsCharsBefore );
		$ol      = '  ' . $origLabel . ':  ' . $origSpacer;
		$cl      = '  ' . $compareLabel . ':  ' . $compareSpacer;
		$sep     = "\n" . str_repeat( '-', self::$compareStringsCharsAfter + self::$compareStringsCharsBefore + strlen( self::$compareStringsPointer ) + strlen( $ol ) + 2 );

		$compare = sprintf(
			$sep . '%8$s%8$s  First difference at position %1$d.%8$s%8$s  %9$s length: %2$d, %10$s length: %3$d%8$s%8$s%4$s%5$s%8$s%6$s%7$s%8$s' . $sep,
			$compare,
			$origLength,
			$newLength,
			$ol,
			substr( $origString, $start, 15 ) . self::$compareStringsPointer . substr( $origString, $compare, self::$compareStringsCharsAfter ),
			$cl,
			substr( $newString, $start, 15 ) . self::$compareStringsPointer . substr( $newString, $compare, self::$compareStringsCharsAfter ),
			"\n",
			$origLabel,
			$compareLabel
		);

		return $compare;
	}

	/**
	 * Noralizes the whitespace in an html string.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $string The html string to normalize.
	 *
	 * @return string          The normalized html string.
	 */
	public function normalizeString( $string ) {
		return trim( preg_replace( array(
			'/[\t\n\r]/', // Remove tabs and newlines
			'/\s{2,}/', // Replace repeating spaces with one space
			'/> </', // Remove spaces between carats
			), array(
			'',
			' ',
			'><',
		), $string ) );
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @since  0.1.0
	 *
	 * @param object $object     Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed             Method return.
	 */
	protected function invokeMethod( $object, $methodName, array $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @since  0.3.0
	 *
	 * @param object $object       Instantiated object that we will run method on.
	 * @param string $propertyName Property name to get.
	 *
	 * @return mixed               Property value.
	 */
	protected function readProperty( $object, $propertyName ) {
		$class = new \ReflectionClass( get_class( $object ) );
		$property = $class->getProperty( $propertyName );
		$property->setAccessible(true);

		return $property->getValue( $object );
	}

	/**
	 * A var_export wrapper for better formatting.
	 *
	 * @since  0.2.0
	 *
	 * @param  mixed  $var   The variable to output.
	 * @param  string $label The label for the output.
	 *
	 * @return string        The outputted variable.
	 */
	public static function varExport( $var, $label = "Given Array:\n\n" ) {
		return $label . preg_replace( array( "/  /", "/\s+=>\s+/" ), array( "\t", " => " ), str_replace( array( 'stdClass::__set_state', 'array (', "=> \n ", "=> \r " ), array( '(object) ', 'array(', '=>' ), var_export( $var, true ) ) ) .';';
	}

	// protected static function ns( $append = '' ) {
	// 	return __NAMESPACE__ . "\\$append";
	// }
}
