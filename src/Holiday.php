<?php

namespace ShibuyaKosuke\LaravelYasumi;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Carbon;
use ReflectionException;
use Yasumi\Exception\MissingTranslationException;
use Yasumi\Yasumi;

/**
 * Class Holiday
 * @package ShibuyaKosuke\LaravelYasumi
 */
class Holiday
{
    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    protected $holidays = [];

    /**
     * Holiday constructor.
     * @param Repository $config
     * @return void
     */
    public function __construct(Repository $config)
    {
        $this->country = $config->get('yasumi.country');
        $this->locale = $config->get('yasumi.locale');
    }
    
    /**
     * @param string $country
     * @return Holiday
     */
    public function country($country)
    {
        $this->country = $country;
        
        return $this;
    }
    
    /**
     * @param string $locale
     * @return Holiday
     */
    public function locale($locale){
        $this->locale = $locale;
        
        return $this;
    }

    /**
     * @param Carbon $carbon
     * @return void
     * @throws MissingTranslationException
     * @throws ReflectionException
     */
    public function getHolidays(Carbon $carbon)
    {
        $holidays = Yasumi::create($this->country, $carbon->year, $this->locale);
        $results = [];
        foreach ($holidays->getHolidays() as $holiday) {
            $results[$holiday->format('Y-m-d')] = $holiday->getName();
        }
        $this->holidays[$carbon->year] = $results;
    }

    /**
     * @param Carbon $carbon
     * @return mixed
     * @throws ReflectionException
     * @throws MissingTranslationException
     */
    public function get(Carbon $carbon)
    {
        $this->getHolidays($carbon);
        return $this->holidays[$carbon->year][$carbon->format('Y-m-d')] ?? null;
    }

    /**
     * @param Carbon $carbon
     * @return bool
     * @throws ReflectionException
     * @throws MissingTranslationException
     */
    public function isHoliday(Carbon $carbon): bool
    {
        $this->getHolidays($carbon);
        return isset($this->holidays[$carbon->year], $this->holidays[$carbon->year][$carbon->format('Y-m-d')]);
    }

    /**
     * @param Carbon $carbon
     * @return bool
     * @throws ReflectionException
     * @throws MissingTranslationException
     */
    public function isDayBeforeHoliday(Carbon $carbon): bool
    {
        $day = $carbon->clone()->addDay();
        return $this->isHoliday($day);
    }
}
