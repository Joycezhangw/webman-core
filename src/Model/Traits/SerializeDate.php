<?php

namespace Landao\WebmanCore\Model\Traits;

use DateTimeInterface;
use Illuminate\Support\Carbon;

trait SerializeDate
{
    /**
     * 重写日期序列化
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format(Carbon::parse($date)->toDateTimeString());
    }
}