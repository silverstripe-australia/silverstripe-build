<?php
/* 
 * 
All code covered by the BSD license located at http://silverstripe.org/bsd-license/
 */

/**
 * Build task that provides some commonly used functionality
 *
 * @author marcus
 */
abstract class SilverStripeBuildTask extends Task {
	/**
	 * @see formatWithColor()
	 * @var array
	 */
	private static $ansiCodes = array(
		'bold'       => 1,
		'fg-black'   => 30,
		'fg-red'     => 31,
		'fg-green'   => 32,
		'fg-yellow'  => 33,
		'fg-blue'    => 34,
		'fg-magenta' => 35,
		'fg-cyan'    => 36,
		'fg-white'   => 37,
		'bg-black'   => 40,
		'bg-red'     => 41,
		'bg-green'   => 42,
		'bg-yellow'  => 43,
		'bg-blue'    => 44,
		'bg-magenta' => 45,
		'bg-cyan'    => 46,
		'bg-white'   => 47
	);

	protected $cleanupEnv = false;
	
	protected function configureEnvFile() {
		// fake the _ss_environment.php file for the moment
		$ssEnv = <<<TEXT
<?php
// Set the \$_FILE_MAPPING for running the test cases, it's basically a fake but useful
global \$_FILE_TO_URL_MAPPING;
\$_FILE_TO_URL_MAPPING[dirname(__FILE__)] = 'http://localhost';

putenv('APACHE_RUN_USER=deploy');

TEXT;

		$envFile = dirname(dirname(__FILE__)).'/_ss_environment.php';
		$this->cleanupEnv = false;
		if (!file_exists($envFile)) {
			file_put_contents($envFile, $ssEnv);
			$this->cleanupEnv = true;
		}
	}

	protected function cleanEnv() {
		if ($this->cleanupEnv) {
			$envFile = dirname(dirname(__FILE__)).'/_ss_environment.php';
			if (file_exists($envFile)) {
				unlink($envFile);
			}
		}
	}

	protected function devBuild() {
		if (file_exists('framework/cli-script.php')) {
			$this->log("Running dev/build");
			$this->exec('php framework/cli-script.php dev/build disable_perms=1');
		}
	}
	
	
	/**
	 * Get some input from the user
	 *
	 * @param string $prompt
	 * @return string
	 */
	protected function getInput($prompt) {
		require_once 'phing/input/InputRequest.php';
		$request = new InputRequest($prompt);
		$request->setPromptChar(':');
		
		$this->project->getInputHandler()->handleInput($request);
		$value = $request->getInput();
		return $value;
	}

	protected function exec($cmd, $returnContent = false, $ignoreError = false) {
		$ret = null;
		$return = null;
		if ($returnContent) {
			$ret = shell_exec($cmd);
		} else {
			passthru($cmd, $return);
		}
		
		if ($return != 0 && !$ignoreError) {
			throw new BuildException("Command '$cmd' failed");
		}
		
		return $ret;
	}

	/**
	 * Formats a buffer with a specified ANSI color sequence if colors are
	 * enabled. (Taken from PHPUnit)
	 *
	 * eg. 	$this->formatWithColor("fg-white, bg-red", "ERROR")
	 *		$this->formatWithColor("fg-black, bg-green", "SUCCESS");
	 *
	 * @param string $color
	 * @param string $buffer
	 *
	 * @return string
	 */
	protected function formatWithColor($color, $buffer)
	{
		$codes   = array_map('trim', explode(',', $color));
		$lines   = explode("\n", $buffer);
		$padding = max(array_map('strlen', $lines));
		$styles  = array();

		foreach ($codes as $code) {
			$styles[] = self::$ansiCodes[$code];
		}

		$style = sprintf("\x1b[%sm", implode(';', $styles));

		$styledLines = array();

		foreach ($lines as $line) {
			$styledLines[] = $style . str_pad($line, $padding) . "\x1b[0m";
		}

		return implode("\n", $styledLines);
	}
}
