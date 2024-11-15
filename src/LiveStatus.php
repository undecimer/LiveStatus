<?php

namespace YOOtheme\LiveStatus;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use YOOtheme\Application;

final class LiveStatus extends CMSPlugin
{
    protected $autoloadLanguage = true;

    public function onAfterInitialise()
    {
        if (!class_exists(Application::class, false)) {
            return;
        }

        $app = Application::getInstance();
        $app->load(__DIR__ . '/../bootstrap.php');
    }
}
