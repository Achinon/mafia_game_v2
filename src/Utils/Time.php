<?php

namespace App\Utils;

use DateTime;
use Exception;

class Time
{
    public static function currentMs(): int
    {
        return round(microtime(true) * 1000);
    }

    public static function msFromMinutes(int $minutes): int
    {
        return $minutes * 60 * 1000;
    }

    public static function msToMinutes(int $ms): int
    {
        return round($ms / 60 / 1000);
    }

    public static function msFromHours(int $hours): int
    {
        return $hours * self::msFromMinutes(1) * 60;
    }

    public static function msToHours(int $ms): int
    {
        return round(self::msToMinutes($ms) / 60);
    }

    public static function msFromDays(int $days): int
    {
        return $days * self::msFromHours(1) * 24;
    }

    public static function msToDays(int $ms): int
    {
        return round(self::msToHours($ms) / 24);
    }

    /**
     * @param int $ms timestamp in milliseconds
     * @return string string of date formatted in 'd-m-Y H:i:s'
     */
    public static function msToDate(int $ms, bool $timezonePrint = false, $format = 'd-m-Y H:i:s'): string
    {
        $timezoneString = '';
        if ($timezonePrint)
            $timezoneString = (new DateTime())
                    ->getTimezone()
                    ->getName() . ' ';

        $timestamp = round($ms / 1000);
        return $timezoneString . date($format, $timestamp);
    }

    public static function getZoneString(): string
    {
        return (new DateTime())
            ->getTimezone()
            ->getName();
    }

    /** @throws Exception */
    public static function msToDateAbsolute(int $ms): string
    {
        return (new DateTime(static::msToDate($ms)))
//            ->format(DateTimeInterface::RFC3339_EXTENDED); exists in php 7.2
            ->format('Y-m-d\TH:i:s.vP');
    }

    /** @throws Exception */
    public static function msToDay(int $ms): string
    {
        return (new DateTime(static::msToDate($ms)))
            ->format('D');
    }

    public static function msInYear($year)
    {
        $dates = [];
        for ($i = 0; $i <= 12; $i++){
            $selectedDate = date("$year-$i");

            $firstDay = date("Y-n-j", strtotime("first day of $selectedDate"));
            $lastDay = date("Y-n-j", strtotime("last day of $selectedDate"));

            $dates[$i]['first_day'] = strtotime($firstDay . ' 00:00:00');
            $dates[$i]['last_day'] = strtotime($lastDay . ' 23:59:59');
        }
        return $dates;
    }
}