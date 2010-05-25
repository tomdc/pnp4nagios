<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
*
* RRDTool Helper Class 
*/
class rrd_Core {
    /*
    * 
    */
    public static function color($num=0 , $alpha='FF'){
        $r = array('99','66','ff','CC','00','33');
        $colors = array();
        $num   = intval($num);
        foreach($r as $ri){
            foreach($r as $gi){
                foreach($r as $bi){
                    $colors[] = sprintf("#%s%s%s%s",$ri,$gi,$bi,$alpha);
                }
            }
        }

        if(array_key_exists($num, $colors)){
            return $colors[$num];
        }else{
            return $colors[0];
        }
    }

    /*
     * Gradient Function
     * Concept by Stefan Triep
     */
    public static function gradient($vname=FALSE, $start_color='#0000a0', $end_color='#f0f0f0', $label=FALSE, $steps=10){
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }
        if(preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i',$start_color,$matches)){
            $r1=hexdec($matches[1]);
            $g1=hexdec($matches[2]);
            $b1=hexdec($matches[3]);
        }else{
            throw new Kohana_exception("Wrong Color Format: '".$start_color."'");   
        }            

        if(preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i',$end_color,$matches)){
            $r2=hexdec($matches[1]);
            $g2=hexdec($matches[2]);
            $b2=hexdec($matches[3]);
        }else{
            throw new Kohana_exception("Wrong Color Format: '".$end_color."'");   
        }            

        $diff_r=$r2-$r1;
        $diff_g=$g2-$g1;
        $diff_b=$b2-$b1;
        $spline =  "";
        $spline_vname = "var".substr(sha1(rand()),1,4);
        
        for ($i=$steps; $i>0; $i--){
            $spline .=  sprintf("CDEF:%s%d=%s,100,/,%d,* ",$spline_vname,$i,$vname,round((100 / $steps) * $i) );
        }    
        for ($i=$steps; $i>0; $i--){
            $factor=$i / $steps;
            $r=round($r1 + $diff_r * $factor);
            $g=round($g1 + $diff_g * $factor);
            $b=round($b1 + $diff_b * $factor);
            if (($i==$steps) and ($label!=FALSE)){
                $spline .=  sprintf("AREA:%s%d#%02X%02X%02X:\"%s\" ", $spline_vname,$i,$r,$g,$b,$label);
            }else{
                $spline .=  sprintf("AREA:%s%d#%02X%02X%02X ", $spline_vname,$i,$r,$g,$b);
            }
        }
        return $spline;
    }


    public static function cut($string, $length=18, $align='left'){
        if(strlen($string) > $length){
            $string = substr($string,0,($length-3))."...";
        }
        if($align == 'left'){
            $format = "%-".$length."s";
        }else{
            $format = "%".$length."s";
        }
        $s = sprintf($format,$string);
        return $s;
    }
    
    public static function line($type=1,$vname=FALSE, $color=FALSE, $text=FALSE, $cf=FALSE){
        $line = "";
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }else{
            $line .= "LINE".$type.":".$vname;
        }
        if($color === FALSE){
            throw new Kohana_exception("Second Paramter 'color' is missing");   
        }else{
            $line .= $color;
        }
        if($text != FALSE){
            $line .= ":\"$text\"";
        }
        if($cf != FALSE){
            $line .= ":".$cf;
        }
        $line .= " ";
        return $line;
    }

    public static function line1($vname=FALSE, $color=FALSE, $text=FALSE, $cf=FALSE){
        return rrd::line(1,$vname, $color,$text, $cf);
    }

    public static function line2($vname=FALSE, $color=FALSE, $text=FALSE, $cf=FALSE){
        return rrd::line(2,$vname, $color,$text, $cf);
    }

    public static function line3($vname=FALSE, $color=FALSE, $text=FALSE, $cf=FALSE){
        return rrd::line(3,$vname, $color,$text, $cf);
    }

    public static function gprint($vname=FALSE, $cf="AVERAGE", $text=FALSE){
        $line = "";
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }
        
        if(is_array($cf)){
            foreach($cf as $key => $val){
                $line .= sprintf("GPRINT:%s:%s:",$vname,$val);
                if($key == sizeof($cf)-1){
                    $line .= '"'.$text.' '.ucfirst(strtolower($val)).'\\l" ';
                }else{
                    $line .= '"'.$text.' '.ucfirst(strtolower($val)).'" ';
                }
            }
        }else{
            $line .= sprintf("GPRINT:%s:%s:",$vname,$cf);
            $line .= '"'.$text.'" ';
        }
        return $line; 
    }

    public static function def($vname=FALSE, $rrdfile=FALSE, $ds=FALSE, $cf="AVERAGE"){
        $line = "";
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }
        if($rrdfile === FALSE){
            throw new Kohana_exception("Second Paramter 'rrdfile' is missing");   
        }
        if($rrdfile === FALSE){
            throw new Kohana_exception("Third Paramter 'ds' is missing");   
        }
        $line = sprintf("DEF:%s=%s:%s:%s ",$vname,$rrdfile,$ds,$cf);
        return $line;
    }

    public static function cdef($vname=FALSE, $rpn=FALSE){
        $line = "";
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }
        if($rrdfile === FALSE){
            throw new Kohana_exception("Second Paramter 'rpn' is missing");   
        }
        $line = sprintf("CDEF:%s=%s ",$vname,$rpn);
        return $line;
    }

    public static function vdef($vname=FALSE, $rpn=FALSE){
        $line = "";
        if($vname === FALSE){
            throw new Kohana_exception("First Paramter 'vname' is missing");   
        }
        if($rrdfile === FALSE){
            throw new Kohana_exception("Second Paramter 'rpn' is missing");   
        }
        $line = sprintf("VDEF:%s=%s ",$vname,$rpn);
        return $line;
    }
} 