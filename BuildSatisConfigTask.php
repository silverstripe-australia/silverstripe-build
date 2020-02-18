<?php

/**
 * Copy a specified set of folders from one location to another
 *
 * @author marcus
 *
 */
class BuildSatisConfigTask extends Task
{
    private $lockfile = null;
    private $outfile = null;

    public function setLockfile($file)
    {
        $this->lockfile = $file;
    }

    public function setOutfile($file)
    {
        $this->outfile = $file;
    }

    public function main()
    {
        $file = $this->lockfile ? $this->lockfile : __DIR__ . '/../composer.lock';
        $lockfile = json_decode(file_get_contents($file), true);

        $satis = [
            'name' => 'my/project-dependencies',
            // depends on php -S 0.0.0.0:6789 from the satis/web directory
            'homepage' => 'http://localhost:6789',
            'repositories' => [
                [
                    "type" => "composer",
                    "url" => "https://packagist.org"
                ]
            ],
            "require-all" => false,
            "require-dependencies" => false,
            "require-dev-dependencies" => false,
            "archive" => [
                "directory" => "dist",
                "format" => "zip"
            ]
        ];

        $require = [];

        foreach ($lockfile['packages'] as $package) {

            $version = $package['version'];
            if ($version == 'dev-master') {
                // see if there's a branch-alias we can use
                if (isset($package['extra']['branch-alias'][$version])) {
                    $version = $package['extra']['branch-alias'][$version];

                    // convert -xdev stuff
                    $version = "~" . str_replace(["x-dev", "-dev"], ["0", ".0"], $version);
                } else {
                    $version = $version . '#' . $package['source']['reference'];
                }
            }



            $require[$package['name']] = $version;
        }

        foreach ($lockfile['packages-dev'] as $package) {

            $version = $package['version'];
            if ($version == 'dev-master') {
                // see if there's a branch-alias we can use
                if (isset($package['extra']['branch-alias'][$version])) {
                    $version = $package['extra']['branch-alias'][$version];

                    // convert -xdev stuff
                    $version = "~" . str_replace(["x-dev", "-dev"], ["0", ".0"], $version);
                } else {
                    $version = $version . '#' . $package['source']['reference'];
                }
            }

            $require[$package['name']] = $version;
        }

        $satis['require'] = $require;

        $output = json_encode($satis, JSON_PRETTY_PRINT);
        $outfile = $this->outfile ? $this->outfile : dirname(__DIR__) . '/mysite/build/satis.json';

        file_put_contents($outfile, $output);
    }
}
