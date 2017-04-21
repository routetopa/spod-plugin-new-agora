<?php

class SPODAGORA_CLASS_Tools
{
    private $avatar_colors =    ['avatar_pink', 'avatar_purple', 'avatar_deeppurple', 'avatar_indigo',
                                 'avatar_lightblue', 'avatar_teal', 'avatar_lightgreen', 'avatar_lime',
                                 'avatar_yellow', 'avatar_amber', 'avatar_deeporange',
                                 'avatar_brown', 'avatar_grey', 'avatar_bluegrey'];

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

    public function process_comment(&$comments, $userId)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $avatars        = $this->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids));

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class      = $userId == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';

            switch ($comment->sentiment)
            {
                case 0 : $comment->sentiment_class = 'neutral'; break;
                case 1 : $comment->sentiment_class = 'satisfied'; break;
                case 2 : $comment->sentiment_class = 'dissatisfied'; break;
            }

            if (!empty($comment->component)) {
                $comment->datalet_class  = 'agora_fullsize_datalet';

                $comment->data = empty($comment->data) ? "''" : $comment->data;

                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'. $comment->component . '",
                                                                    ' . $comment->params . ',
                                                                    ['. $comment->fields . '],
                                                                    ' . $comment->data . ',
                                                                    "agora_datalet_placeholder_' . $comment->id . '");');
            }

        }

        return $comments;
    }

    public function process_comment_include_datalet(&$comments, $user_id)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $avatars        = $this->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids));

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class       = $user_id == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';
            $comment->sentiment_class = $comment->sentiment == 0 ? 'neutral' : ($comment->sentiment == 1 ? 'satisfied' : 'dissatisfied');
            $comment->datalet_class   = '';
            $comment->datalet_html    = '';

            if (isset($comment->component)) {
                $comment->datalet_class = 'agora_fullsize_datalet';
                $comment->datalet_html  = $this->create_datalet_code($comment);
            }else{
                $comment->component = '';
            }

        }

        return $comments;
    }

    private function create_datalet_code($comment)
    {
        $params = json_decode($comment->params);
        $html  = '';//"<link rel='import' href='".SPODPR_COMPONENTS_URL."datalets/{$comment->component}/{$comment->component}.html' />";
        $html .= "<{$comment->component} ";

        foreach ($params as $key => $value){
            $html .= $key."='".$value."' ";
        }

        //CACHE
        $html .= " data='{$comment->data}'";
        $html .= " ></{$comment->component}>";

        return $html;
    }

    public function get_all_datalet_definitions()
    {
        $definitions = '';

        $ch = curl_init($preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list')->defaultValue);//1000 limit!
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (200 == $retcode) {
            $data = json_decode($res, true);
            foreach ($data as $datalet)
            {
                $definitions .= "<link rel='import' href='{$datalet['url']}{$datalet['name']}.html' />";
            }
        }

        return $definitions;
    }

}