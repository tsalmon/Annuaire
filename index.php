<?php
/*
* @author SALMON Thomas - July/August 2014
* More details about me : tsalmon.fr
* To lean how to use this source-code, read `README.txt` file (in french)
*/



function suppr_accents($str, $encoding='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#&[^;]+;#', '', $str);
    return $str;
}

if(isset($_GET["nom"])) {
    header("location: ./index.php?p=".str_replace(" ", "_", trim($_GET["nom"])));
}

include_once($_SERVER['APPL_PHYSICAL_PATH'].'include.php');
require "application/config/config.php";
require "application/Controller.php";

if(DEBUG_MODE){
    require "application/Test.php";
    new Test();
} else {
    require "application/Annuaire.php";
    new Annuaire();
}
?>
