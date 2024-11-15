<?php

use Joomla\CMS\Installer\InstallerScript;

class PlgSystemLiveStatusInstallerScript extends InstallerScript
{
    public function install($parent)
    {
        $this->cleanCache();
    }

    public function update($parent)
    {
        $this->cleanCache();
    }

    public function uninstall($parent)
    {
        $this->cleanCache();
    }

    protected function cleanCache()
    {
        $cache_dir = JPATH_CACHE . '/plg_system_livestatus';
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($cache_dir);
        }
    }
}
