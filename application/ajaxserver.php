<?php
require_once("config/config.php");
require_once("ODBCHelper.php");
header('Content-Type: text/xml;charset=utf-8');
echo(utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>"));

function suppr_accents($str, $encoding='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#&[^;]+;#', '', $str);
    return $str;
}

$server = "localhost";
$user = "root";
$password = "";
$base = "annuaire";
$liste = array();
$debut = "";

ODBCHelper::getInstance();

if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} elseif (isset($_GET['fonction'])) {
    $debut = utf8_decode($_GET['fonction']);
}
$debut = strtolower($debut);
$debut = str_replace("'", "''", $debut); //escape single quote to SQL server

if(isset($_GET["fonction"])){
    $debut = "%".str_replace("_", "%", $debut)."%";
    $resultat = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE fonction LIKE '".$debut."' AND affichage = '1'"); 
    while($fetch_result = ODBCHelper::fetch($resultat)){
        $fetch_result["prenom"] = suppr_accents($fetch_result["prenom"]);
        $fetch_result["nom"] = suppr_accents($fetch_result["nom"]);
        array_push($liste, str_replace(" ", "-", $fetch_result["nom"])." ".str_replace(" ", "-", $fetch_result["prenom"]));
    }
} else{
    $resultat = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE (nom LIKE '%".$debut."%' OR prenom LIKE '%".$debut."%') AND affichage = '1'"); 
    while($fetch_result = ODBCHelper::fetch($resultat)){
        $fetch_result["prenom"] = suppr_accents($fetch_result["prenom"]);
        $fetch_result["nom"] = suppr_accents($fetch_result["nom"]);
        array_push($liste, str_replace(" ", "-", $fetch_result["nom"])." ".str_replace(" ", "-", $fetch_result["prenom"]));
    }
}

function generateOptions($debut,$liste) {
    if(isset($_GET["debut"])){
        $MAX_RETURN = 10;
    } else {
        $MAX_RETURN = count($liste);
    }
    $i = 0;
    for($i = 0; $i < count($liste) && $i < $MAX_RETURN; $i++) {
        echo(utf8_encode("<option>".($liste[$i])."</option>"));
    }
}
 
generateOptions($debut,$liste);
 
echo("</options>");
?>
