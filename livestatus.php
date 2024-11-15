<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\Event\DispatcherInterface;

// Load bootstrap file for autoloading
require_once __DIR__ . '/bootstrap.php';

// Invoke the service provider
$provider = require __DIR__ . '/services/provider.php';

// Register with the update sites
Factory::getApplication()
    ->bootComponent('installer')
    ->createUpdateSites(
        __DIR__ . '/livestatus.xml',
        'plugin',
        'plg_system_livestatus',
        'system',
        1,
        'plugin',
        'livestatus'
    );
