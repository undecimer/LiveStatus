<?php
/**
 * @package     YOOtheme Pro
 * @subpackage  Live Status Element
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseInterface;

class PlgSystemLivestatusInstallerScript extends InstallerScript
{
    /**
     * Extension script constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        // Define the minimum versions to be supported.
        $this->minimumJoomla = '4.0';
        $this->minimumPhp = '7.4.0';
    }

    /**
     * Called before any type of action
     *
     * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
     * @param   InstallerAdapter  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($type, $parent)
    {
        if ($type === 'uninstall') {
            return true;
        }

        // Check for the minimum PHP version before continuing
        if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Could not install Live Status plugin in PHP ' . PHP_VERSION . '. PHP version ' . $this->minimumPhp . ' or higher is required.',
                'error'
            );

            return false;
        }

        // Check for the minimum Joomla version before continuing
        if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Could not install Live Status plugin. Joomla version ' . $this->minimumJoomla . ' or higher is required.',
                'error'
            );

            return false;
        }

        return true;
    }

    /**
     * Called on installation
     *
     * @param   InstallerAdapter  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install($parent)
    {
        $this->cleanCache();
        return true;
    }

    /**
     * Called on uninstallation
     *
     * @param   InstallerAdapter  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function uninstall($parent)
    {
        $this->cleanCache();
        return true;
    }

    /**
     * Called on update
     *
     * @param   InstallerAdapter  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update($parent)
    {
        $this->cleanCache();
        return true;
    }

    /**
     * Called after any type of action
     *
     * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
     * @param   InstallerAdapter  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($type, $parent)
    {
        $this->cleanCache();
        return true;
    }

    /**
     * Remove a directory and all its contents
     *
     * @param   string  $dir  The directory to remove
     *
     * @return  void
     */
    private function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Clean Joomla cache
     *
     * @return  void
     */
    private function cleanCache()
    {
        $app = Factory::getApplication();
        $options = ['defaultgroup' => '', 'cachebase' => JPATH_ADMINISTRATOR . '/cache'];
        $cache = Factory::getContainer()->get(CacheController::class);
        $cache->clean('_system', 'group');
        $app->cleanCache('_system', 'group');
    }
}
