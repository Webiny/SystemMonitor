<?php

namespace Apps\SystemMonitor\Php\Entities;

use Apps\Webiny\Php\Lib\Api\ApiContainer;
use Apps\Webiny\Php\Lib\Entity\AbstractEntity;
use Apps\Webiny\Php\Lib\Entity\Indexes\IndexContainer;
use Apps\Webiny\Php\RequestHandlers\ApiException;
use Webiny\Component\Mongo\Index\SingleIndex;

/**
 * Class ApiSnapshot
 *
 * @property string  $id
 * @property integer numRequests
 * @property integer hitCount
 * @property integer missCount
 * @property integer timeSlot
 * @property integer totalTime
 */
class ApiSnapshot extends AbstractEntity
{
    protected static $classId = 'SystemMonitor.Entities.ApiSnapshot';
    protected static $collection = 'SystemMonitorApiSnapshots';
    protected static $i18nNamespace = 'SystemMonitorApiSnapshots';

    public function __construct()
    {
        parent::__construct();

        $this->attributes->remove('deletedOn', 'deletedBy', 'modifiedOn', 'modifiedBy', 'createdBy');

        $this->attr('numRequests')->integer()->setDefaultValue(0)->setToArrayDefault();
        $this->attr('totalTime')->float()->setDefaultValue(0)->setToArrayDefault();
        $this->attr('hitCount')->integer()->setDefaultValue(0)->setToArrayDefault();
        $this->attr('missCount')->integer()->setDefaultValue(0)->setToArrayDefault();
        $this->attr('timeSlot')->integer()->setToArrayDefault();
    }

    protected function entityApi(ApiContainer $api)
    {
        parent::entityApi($api);

        $api->get('/stats/{preset}', function ($preset) {

            switch ($preset) {
                case '1h':
                    $query = ['timeSlot' => ['$gte' => time() - 3600, '$lte' => time()]];
                    break;

                case '3h':
                    $query = ['timeSlot' => ['$gte' => time() - 10800, '$lte' => time()]];
                    break;

                case '6h':
                    $query = ['timeSlot' => ['$gte' => time() - 21600, '$lte' => time()]];
                    break;

                case '24h':
                    $query = ['timeSlot' => ['$gte' => time() - 86400, '$lte' => time()]];
                    break;

                case '7d':
                    $query = ['timeSlot' => ['$gte' => time() - 604800, '$lte' => time()]];
                    break;

                case '30d':
                    $query = ['timeSlot' => ['$gte' => time() - 2592000, '$lte' => time()]];
                    break;

                default:
                    throw new ApiException('Invalid preset selected.');
                    break;
            }

            return self::find($query)->toArray();
        });
    }


    protected static function entityIndexes(IndexContainer $indexes)
    {
        parent::entityIndexes($indexes);

        // delete records after 2 months
        $indexes->add(new SingleIndex('createdOn', 'createdOn', null, false, false, 5184000));
        $indexes->add(new SingleIndex('timeSlot', 'timeSlot'));
    }


    public function logRequest($responseTime, $cacheHit)
    {
        $entry = $this->getEntry();
        $entry->numRequests++;
        $entry->totalTime += $responseTime;
        if ($cacheHit) {
            $entry->hitCount++;
        } else {
            $entry->missCount++;
        }

        $entry->save();
    }

    /**
     * Check is stats entry exists for the current slot and returns the instance.
     * In case the entry doesn't exist, a new instance will be created.
     *
     * @return ApiSnapshot
     */
    private function getEntry()
    {
        // get time slot
        $minuteSlot = date('i');
        $min = 0;
        if ($minuteSlot >= 10 && $minuteSlot <= 19) {
            $min = 10;
        } elseif ($minuteSlot >= 20 && $minuteSlot <= 29) {
            $min = 20;
        } elseif ($minuteSlot >= 30 && $minuteSlot <= 39) {
            $min = 30;
        } elseif ($minuteSlot >= 40 && $minuteSlot <= 49) {
            $min = 40;
        } elseif ($minuteSlot >= 50 && $minuteSlot <= 59) {
            $min = 50;
        }

        $timeSlot = strtotime(date('Y-m-d H:00:00')) + ($min * 60);

        $result = $this->findOne(['timeSlot' => $timeSlot]);
        if (!$result) {
            $result = new self;
            $result->timeSlot = $timeSlot;
        }

        return $result;
    }
}