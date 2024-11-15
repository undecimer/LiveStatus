<?php

defined('_JEXEC') or die;

use YOOtheme\LiveStatus\Extension\LiveStatus;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// Register autoloader for YOOtheme\LiveStatus namespace
if (!class_exists(LiveStatus::class)) {
    require_once dirname(__DIR__) . '/src/Extension/LiveStatus.php';
}

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $pluginParams = PluginHelper::getPlugin('system', 'livestatus');
                $dispatcher = $container->get(DispatcherInterface::class);
                $app = Factory::getApplication();

                $plugin = new LiveStatus(
                    $dispatcher,
                    (array) $pluginParams,
                    $app
                );

                return $plugin;
            }
        );
    }
};
