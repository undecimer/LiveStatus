<?php

defined('_JEXEC') or die;

use YOOtheme\LiveStatus\Extension\LiveStatus;

return function () {
    return new LiveStatus(
        $this->get('dispatcher'),
        (array) $this->get('params'),
        $this->get('app')
    );
};
