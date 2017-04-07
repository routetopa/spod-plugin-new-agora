<?php

class SPODAGORA_CLASS_Tools
{
    private $avatar_colors = ['avatar_orange', 'avatar_purple', 'avatar_lime'];
    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return OW::getLanguage()->text('spodagora', 'yesterday'). " " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }

    public function array_push_return($array, $val)
    {
        array_push($array, $val);
        return $array;
    }

    public function check_value($params)
    {
        foreach ($params as $var)
        {
            if(!isset($_REQUEST[$var]))
                return false;
        }

        return true;
    }

    public function get_hashtag($str)
    {
        preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $str, $matches);
        return array_unique($matches[2]);
    }

    public function process_avatar($avatars)
    {
        if (empty($avatars))
            return;

        foreach ($avatars as &$avatar)
        {
            if(strpos( $avatar['src'], 'no-avatar'))
            {
                $avatar['css'] = 'no_img ' . $this->avatar_colors[$avatar["userId"] % count($this->avatar_colors)];
                $avatar['initial'] = strtoupper($avatar['title'][0]);
                $avatar['src'] = '';
            }
            else
            {
                $avatar['css'] = '';
                $avatar['initial'] = '';
            }

        }

        return $avatars;
    }

}