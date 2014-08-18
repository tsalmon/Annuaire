<?php

require_once("Personne.php");

class Organization{
	public  static $leader;
	private static $all_personnes;
	private static $all_fonctions;

    public function __construct(){}

    public function toutVoir(){
    	$liste = array();
		$res = ODBCHelper::exec("SELECT nom, prenom, fonction, batiment, salle, telephone, portable, mail  FROM ".DB_TABLE_USER." ORDER BY nom");
		Organization::$all_personnes = array();		
		foreach (ODBCHelper::fetchAll($res) as $key => $value) {
			array_push($liste, $value);
		}
		return $liste;		
    }

	public static function liste_personne(){
		if(isset(Organization::$all_personnes)){
			return Organization::$all_personnes;
		}
		$res = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." ORDER BY nom");
		Organization::$all_personnes = array();		
		foreach (ODBCHelper::fetchAll($res) as $key => $value) {
			array_push(Organization::$all_personnes, 
						new Personne( ucfirst(strtolower(suppr_accents($value["nom"]))), 
									 ucfirst(strtolower(suppr_accents($value["prenom"]))), false
						)
			);
		}
		return Organization::$all_personnes;
	}

	public function searchPersonneByAttr($cond_search){
		$result_personnes = array();
		$str_cond = "";
		foreach ($cond_search as $key => $value) {
			$str_cond .= "$key LIKE '%".$value."%' AND";
		}
        $resultat = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE $str_cond ".AFFICHAGE."");
        foreach (ODBCHelper::fetchAll($resultat) as $key => $value) {
        	array_push($result_personnes, new Personne($value["nom"], $value["prenom"], true));
        }
        return $result_personnes;
	}

	public static function liste_fonction(){
		if(isset(Organization::$all_fonctions)){
			return Organization::$all_fonctions;
		}

		Organization::$all_fonctions = array();

        $resultat = ODBCHelper::exec("SELECT DISTINCT fonction FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." ORDER BY fonction");
        foreach (ODBCHelper::fetchAll($resultat) as $key => $value) {
        	array_push(Organization::$all_fonctions, $value["fonction"]);
        }
        return Organization::$all_fonctions;
	}

	public function personne_rewriting($nom, $prenom){
		$nom = suppr_accents(trim($nom));
		$prenom = suppr_accents(trim($prenom));
		return str_replace(" ", "-", $nom).'_'.str_replace(" ", "-", $prenom);
	}

	public function search_personne_rewrited($personne){
		$personne = str_replace("-", " ", $personne);
		$handler_personne = explode("_", $personne); 
		if(count($handler_personne) == 1){
			return false;
		}

		try{
			return new Personne($handler_personne[0], $handler_personne[1], true);
		} catch(Exception $e) {
			return false;
		}
	}

    public static function isEmpty() {
        return $this->leader === null;
    }

    public static function getInfo($str_leadername){
		$res = ODBCHelper::exec("SELECT IDUSER, nom, prenom, responsable, telephone, mail, fonction  FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." AND IDUSER = '".$str_leadername."'");
		$fetch = ODBCHelper::fetch($res);
		return $fetch;
	}

	public function getAllInfos(){
		$res = ODBCHelper::exec("SELECT nom, prenom, fonction, mail, telephone, portable, salle, uo  FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." ORDER BY nom");
		$fetch = ODBCHelper::fetchAll($res);
		return $fetch;
	}

	public static function makePersonne($info, $chef){
		$personne = new Personne($info["nom"], $info["prenom"]);
		$personne->chef = $chef;
		$personne->equipe = Organization::makeEquipe($info['IDUSER']);
		return $personne;
	}

	public static function makeEquipe($str_leadername){
		$equipe = array();

		$resultat = ODBCHelper::exec("SELECT nom, prenom, responsable, telephone, mail, fonction, IDUSER  FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." AND responsable LIKE '".$str_leadername."'");
		while($fetch_result = ODBCHelper::fetch($resultat)) {
			array_push($equipe, Organization::makePersonne($fetch_result, $str_leadername)); 		
		}
		return $equipe;
	}

	public static function explore($personne){
		$link_personne = str_replace(" ", "-",suppr_accents($personne->nom))."_".str_replace("-", " ", suppr_accents($personne->prenom));
		echo '<a href="index.php?p='.$link_personne.'">'.$personne->nom.' '.$personne->prenom.'</a>';
		if(!isset($personne->equipe)){
			return;
		}
		echo "<ul>";
			foreach ($personne->equipe as $key => $value) {
				echo "<li>";
				Organization::explore($value);
				echo "</li>";
			}
		echo "</ul>";
	}
}

?>