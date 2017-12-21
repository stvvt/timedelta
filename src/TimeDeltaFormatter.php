<?php

namespace stv;

class TimeDeltaFormatter
{
    /**
     * Base time for calculating deltas
     *
     * @var int Unix timestamp
     */
    public static $now;

    /**
     * Time to convert, relative to self::$now
     *
     * @var int Unix timestamp
     */
    protected $time;

    /**
     * @var UtilitiesInterface
     */
    public static $utilities;

    /**
     * Internal structure holding time delta parts.
     *
     * Time parts are the same returned by PHP function @see getdate()
     *
     * @var array
     */
    protected $deltaParts = array();

    const A_DAY_IN_SECONDS = 86399; // = (24h in seconds) minus one second

    const DIR = [
        'PAST' => false,
        'FUTURE' => true
    ];

    public function __construct($time)
    {
        $this->setTime($time);
    }

    public function round()
    {
        $r = $this->deltaParts;

        // Round minutes
        switch (true) {
            case ($r['s'] > 50):
                $r['i']++;
                break;
            case ($r['i'] >= 26 && $r['i'] <= 34):
                // 26 - 34 min
                $r['i'] = 30;
                break;
            case ($r['i'] >= 55):
                // 55 - 59 min
                $r['i'] = 0;
                $r['H']++;
                break;
        }

        if ($r['H'] >= 1 && $r['i'] <= 5) {
            $r['i'] = 0;
        }

        // Round hours
        if (!$this->isToday() && !$this->isYesterday() && !$this->isTomorrow()) {
            $r['d'] = max($r['d'], 2);
            $r['i'] =
            $r['H'] = 0;
        }

        // Round days
        if ($r['d'] > 25) {
            $r['d'] =
            $r['i'] =
            $r['H'] = 0;
            $r['m']++;
        }

        // Round months
        if ($r['m'] >= 12) {
            $r['m'] %= 12;
            if ($r['m'] >= 5 || $r['m'] <= 7) {
                $r['m'] = 6;
            }
            $r['d'] =
            $r['i'] =
            $r['H'] = 0;
            $r['Y']++;
        }

        if ($r['Y'] > 1 && $r['m'] < 2) {
            $r['m'] = 0;
        }

        // Always ignore seconds
        $r['s'] = 0;

        return $r;
    }

    public function say($r)
    {
        $inWords = '';
        $prependDir = true;

        switch (true) {
            case $r['Y'] == 1 && $r['m'] == 0:
                $inWords = self::$utilities::__('година');
                break;
            case $r['Y'] == 1:
                $inWords = self::$utilities::__('година и %d мес.', $r['m']);
                break;
            case $r['Y'] > 1 && $r['m'] == 0:
                $inWords = self::$utilities::__('години', $r['Y']);
                break;
            case $r['Y'] > 1:
                $inWords = self::$utilities::__('%d години и %d мес.', $r['Y'], $r['m']);
                break;

            case $r['m'] == 1 && $r['d'] == 0:
                $inWords = self::$utilities::__('месец');
                break;
            case $r['m'] == 1 :
                $inWords = self::$utilities::__('месец и %d дни', $r['d']);
                break;
            case $r['m'] > 1 && $r['d'] == 0:
                $inWords = self::$utilities::__('%d месеца', $r['m']);
                break;
            case $r['m'] > 1 && $r['d'] < 5:
                $inWords = self::$utilities::__('близо %d месеца', $r['m']);
                break;
            case $r['m'] > 1 && $r['d'] > 25 :
                $inWords = self::$utilities::__('близо %d месеца', $r['m']+1);
                break;
            case $r['m'] > 4:
                $inWords = self::$utilities::__('%d месеца', $r['m']);
                break;
            case $r['m'] > 1:
                $inWords = self::$utilities::__('%d месеца и %d дни', $r['m'], $r['d']);
                break;

            case $r['d'] > 1:
                $inWords = self::$utilities::__('%d дни', $r['d']);
                break;

            case $this->deltaHr <= 6 && $r['H'] >= 1:
                if ($r['H'] == 1) {
                    $inWords = self::$utilities::__('час');
                } else {
                    $inWords = self::$utilities::__('%d часа', $r['H']);
                }
                if ($r['i'] > 1) {
                    $inWords .= ' ' . self::$utilities::__('и %d мин.', $r['i']);
                }
                break;

            case $this->deltaHr > 6:
                assert ($r['d'] <= 1);
                $time = date('H:i', $this->time);
                if ($this->isToday()) {
                    $inWords = self::$utilities::__('днес');
                } elseif ($this->isTomorrow()) {
                    $inWords = self::$utilities::__('утре');
                } elseif ($this->isYesterday()) {
                    $inWords = self::$utilities::__('вчера');
                }

                $inWords .= ' ' . self::$utilities::__('в %s ч.', $time);
                $prependDir = false;
                break;


            case $r['i'] > 1:
                $inWords = self::$utilities::__('%d минути', $r['i']);
                break;
            case $r['i'] == 1:
                $inWords = self::$utilities::__('минута');
                break;

            default:
                $inWords = self::$utilities::__('сега');
                $prependDir = false;
        }

        if ($prependDir) {
            switch ($this->dir) {
                case self::DIR['FUTURE']:
                    $dirText = 'след';
                    break;
                case self::DIR['PAST']:
                    $dirText = 'преди';
                    break;
            }

            $inWords = $dirText . ' ' . $inWords;
        }

        return $inWords;
    }

    protected function isToday()
    {
        return date('Y-m-d', self::$now) == date('Y-m-d', $this->time);
    }

    protected function isTomorrow()
    {
        return date('Y-m-d', self::$now + self::A_DAY_IN_SECONDS) == date('Y-m-d', $this->time);
    }

    protected function isYesterday()
    {
        return date('Y-m-d', self::$now - self::A_DAY_IN_SECONDS) == date('Y-m-d', $this->time);
    }

    public function setTime($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $this->time = $time;

        // re-calculate after time change
        if ($this->time < self::$now) {
            $this->dir = self::DIR['PAST'];
        } else {
            $this->dir = self::DIR['FUTURE'];
        }

        $delta = abs($this->time - self::$now);
        $this->deltaParts = array(
            's' => $delta % 60,
            'i' => intval($delta / 60) % 60,
            'H' => intval($delta / (60 * 60)) % 24,
            'd' => intval($delta / (60 * 60 * 24)) % 30,
            'm' => intval($delta / (60 * 60 * 24 * 30)) % 12,
            'Y' => intval($delta / (60 * 60 * 24 * 30 * 12)),
        );

        $this->deltaSec = abs($this->time - self::$now);
        $this->deltaMin = $this->deltaSec / 60;
        $this->deltaHr  = $this->deltaMin / 60;
        $this->deltaDay = $this->deltaHr / 24;
    }

    public static function convert($time)
    {
        $self = new self($time);

        return $self->say($self->round());
    }

    public function __toString() {
        return $this->say($this->round());
    }
}

TimeDeltaFormatter::$now = time();