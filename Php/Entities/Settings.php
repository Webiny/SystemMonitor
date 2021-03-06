<?php

namespace Apps\SystemMonitor\Php\Entities;

/**
 * Class Settings
 */
class Settings extends \Apps\Webiny\Php\Entities\AbstractSettings
{
    protected static $classId = 'SystemMonitor.Entities.Settings';
    protected static $key = 'system-monitor';

    public function save()
    {
        /**
         * @see http://php.net/manual/en/mongodb.setprofilinglevel.php
         */
        if (!$this->settings->keyNested('dbMonitor.status', false, true)) {
            $this->wDatabase()->command(['profile' => 0]);
        } else {
            $this->wDatabase()->command(['profile' => 1, 'slowms' => intval($this->settings->keyNested('dbMonitor.slowQueryThreshold'))]);
        }

        return parent::save();
    }
}