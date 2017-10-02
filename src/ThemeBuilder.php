<?php

namespace Symbiote\Build;

use Composer\Script\Event;

class ThemeBuilder {

    public static function run(Event $event) {
        $baseDir = getcwd();
        $themeDir = $baseDir . '/themes';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo "Skipping yarning on windows, please run manually from theme folder\n";
            return;
        }

        if (is_dir($themeDir)) {
            $themes = glob("$themeDir/*");

            foreach ($themes as $theme) {
                $package = $theme . '/package.json';
                echo "Looking in $theme\n";
                if (file_exists($package) && !is_dir($theme .'/node_modules')) {
                    chdir($theme);
                    $packageJson = file_get_contents($package);
                    if (!strlen($packageJson)) {
                        continue;
                    }
                    $packageDetail = json_decode($packageJson, true);
                    if (isset($packageDetail['scripts']['build']) && `which yarn`) {
                        echo "Build target found, running yarn\n";
                        echo exec("yarn install && yarn build");
                        echo "\n";
                    }
                    
                    chdir($baseDir);

                }
            }
        }
    }

}
