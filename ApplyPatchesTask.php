<?php
/* All code covered by the BSD license located at http://silverstripe.org/bsd-license/ */

include_once dirname(__FILE__).'/SilverStripeBuildTask.php';

/**
 * Task for applying patch files from a folder to a source trunk
 *
 * Assumes that the patches were created relative to the root folder of the application
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class ApplyPatchesTask extends SilverStripeBuildTask {
    private $patchDir;

	public function setPatchDir($v) {
		$this->patchDir = $v;
	}

	public function main() {
		// go through each patch and apply it in the root of the app
		if (!is_dir($this->patchDir)) {
			throw new Exception("Invalid patch directory setting");
		}

		// read all the files in
		$patches = scandir($this->patchDir);

		
		foreach ($patches as $patch) {
			if ($patch == '.' || $patch == '..') {
				continue;
			}

			$file = $this->patchDir . '/' . $patch;

			if (is_file($file)) {
				$exec_output = [];
				$exec_check_output = [];

				$exec_check_command = "patch --strip 0 --no-backup-if-mismatch --input $file --reverse --dry-run";
				$exec_command = "patch --strip 0 --no-backup-if-mismatch --input $file";

				$this->log("Run: $exec_command");
				exec($exec_check_command, $exec_check_output, $exec_check_return);

				if ($exec_check_return) {
					exec($exec_command, $exec_output, $exec_return);
					$this->log(implode("\n", $exec_output) . "\n\n");

					if ($exec_return) {
						throw new Exception($this->formatWithColor("fg-white, bg-red", "The patch produced errors"));
					}
				} else {
					$this->log("This patch is already applied - skipping\n", Project::MSG_WARN);
					$this->project->setProperty('patches_applied', true);
				}
			}
		}
	}
}
