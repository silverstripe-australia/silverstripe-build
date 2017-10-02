<?php

include_once dirname(__FILE__).'/SilverStripeBuildTask.php';

/**
 * Execute the SilverStripe test suite ala the phpunit task
 *
 * @author marcus
 *
 */
class SilverStripeTestTask extends SilverStripeBuildTask
{
	private $module = '';
	private $testcase = '';
	private $doFlush = false;
	private $doBuild = false;
	private $coverage = false;
	
	public function setModule($v) {
		if (strpos($v, '$') === false) {
			$this->module = $v;
		}
	}
	
	public function setTestCase($v) {
		if (strpos($v, '$') === false) {
			$this->testcase = $v;
		}
	}
	
	public function setFlush($v) {
		if (strpos($v, '$') === false) {
			$this->doFlush = $v;
		}
	}
	
	public function setBuild($v) {
		if (strpos($v, '$') === false) {
			$this->doBuild = $v;
		}
	}
	
	public function setCoverage($v) {
		if (strpos($v, '$') === false) {
			$this->coverage = $v;
		}
	}

	/**
	 * The main entry point
	 *
	 * @throws BuildException
	 */
	function main()
	{
		
		$testCmd = 'dev/tests/all';
		
		if ($this->module != '') {
			$testCmd = 'dev/tests/module/'.$this->module;
		}
		
		if ($this->testcase != '') {
			$testCmd = 'dev/tests/'.$this->testcase;
		} 

		$build = "build=1";
		if (!$this->doBuild) {
			$build = "build=0";
		}
		
		$flush = "flush=1";
		if (!$this->doFlush) {
			$flush = "flush=0";
		}
		
		if ($this->coverage) {
			$testCmd = str_replace('dev/tests/', 'dev/tests/coverage/', $testCmd);
		}
		
		$testCmd .= " disable_perms=1 $flush $build";

		echo "Exec $testCmd\n";
		
		// simply call the php ss-cli-script.php dev/tests/all. We ignore the errors because
		// the test report script picks them up later on. 
		$this->exec('php framework/cli-script.php '.$testCmd, false, true);
		
		/*if (preg_match("/(\d+) tests run: (\d+) passes, (\d+) fails, and (\d+) exceptions/i", $output, $matches)) {
			print_r($matches);
		}*/
	}
}
