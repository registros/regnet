<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Test;

/**
 * Description of Test3
 *
 * @author Adrian Zurkiewicz
 */
class Test3 {
	
	/**
	 *
	 * @var int
	 */
	private $foo;
	
	/**
	 *
	 * @var int 
	 */
	private $bar;

	/**
	 * 
	 * @param int $foo
	 * @param int $bar
	 */
	function __construct(int $foo = 0, int $bar = 0) {
		$this->foo = $foo;
		$this->bar = $bar;
	}

	
	/**
	 * Test method.<br>
	 * 
	 * WARNING! It is only test method.
	 * 
	 * <code>
	 * $test->get(1);
	 * </code>
	 * 
	 * @param int $baz This is baz value
	 * @return array|null Return result as array
	 * @throws Exception
	 */
	public function get(int $baz = 0): ?array {
		
		$result = [];
		$result[] = 'Hello World, from ' . __METHOD__ . ' on ' . gethostname();
		$result[] = "foo: {$this->foo}";
		$result[] = "bar: {$this->bar}";
		$result[] = "baz: {$baz}";
		$result[] = "time: " . time();
		
		return $result;
		
	}
	
	
	/**
	 * 
	 */
	public function myInternalFunction() {
		
	}	
	

	/**
	 * This method can only be accessed from my network.
	 */
	public function myRestrictedMethod1() {
	
	}
	
	/**
	 * This method can only be accessed from certain networks.
	 */
	public function myRestrictedMethod2() {
	
	}	
	
	/**
	 * This method can only be accessed width API key.
	 */
	public function myRestrictedMethod3() {
	
	}	
	
	/**
	 * This method is deprecated
	 * 
	 * @deprecated
	 */
	public function myOldMethod() {
		
		
	} 
	
	
}
