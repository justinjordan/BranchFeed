<?php


class Time
{
    public static function FormatDate( $dateString )
    {
        $postDate = strtotime( $dateString );
        $elapsed = time() - $postDate;

        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ( $tokens as $unit => $text )
        {
            if ( $elapsed < $unit ) continue;
            
            $numberOfUnits = floor( $elapsed/$unit );
            
            return $numberOfUnits.' '.$text.(($numberOfUnits>1 || $numberOfUnits==0)?'s':''). ' ago';
        }
        
        return '';
    }
}