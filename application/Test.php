<style type="text/css">
	tr:nth-child(even) {background: #CCC}
	tr:nth-child(odd) {background: #FFF}
	#view_fonction { cursor: pointer; }

</style>

<?php
class Test extends Controller{	
	private $MODELorganization;

	function __construct(){
        parent::__construct();     
		$this->MODELorganization = $this->loadModel('Organization'); //test connecting
        $this->tests();
	}

	/*there is more than 400 rows in the table, so it will be slow*/
	public function tests(){
   		require 'Application/_templates/header.php';
		echo "<h1>Tests</h1>";
				
		$this->test_UTF8_fromDB();
        $this->test_IDUSER_IsUnique();
        $this->test_InfoError();
        $this->test_ReconnaissanceFonction();
    	
    	require 'Application/_templates/footer.php'; 
	}

	public function test_UTF8_fromDB(){
		echo "<h2>Test de reception de chaines en UTF8 dans la DB</h2>";
		$tests = array("chargé d'étude", "ingénieur", "étè", "noël", "ìnt");
		for($i = 0; $i < count($tests); $i++){
			$tests[$i] = str_replace("'", "''", $tests[$i]); //replace single quote "d'étude"
			$resultat = ODBCHelper::exec("SELECT '".$tests[$i]."'");
			$tests[$i] = str_replace("''", "'", $tests[$i]); //re-replace for compare
			$fetch = ODBCHelper::fetch($resultat);

			if(isset($fetch[$tests[$i]])){
				$fetch = utf8_decode($fetch[$tests[$i]]);
			} else {
				var_dump($tests[$i]);
				echo "<br/>";
				var_dump($fetch);
			}

			if($fetch == $tests[$i]){
				echo "<p>ok ($fetch)</p>";
				continue;
			} 	
			echo "<p>Ne renvoit pas \"".$tests[$i]."\" (";
			var_dump($fetch);
			echo ")</p>	";
		}
	}

    public function suppr_accents($str, $encoding='utf-8'){
        $str = htmlentities($str, ENT_NOQUOTES, $encoding);
        $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);
        return $str;
    }
	

	function test_InfoError(){
		echo "<h2>Test d'erreurs sur la récupération d'informations dans la table ".DB_TABLE_USER."</h2>";
		echo "<h3>liste des personnes qui presentent un dysfonctionnement</h3>";
		$ok = true;
		$liste_personne = $this->MODELorganization->liste_personne();
		echo "<table>";
		echo "<tr><th>Nom</th><th>Prenom</th><th>Nom_Prenom </th></tr>";
		foreach ($liste_personne as $personne) {
			$personne_rewrited = $this->MODELorganization->personne_rewriting($personne->nom, $personne->prenom);			
			$recherche = $this->MODELorganization->search_personne_rewrited($personne_rewrited);
			if(!$recherche->printPersonne()){
				exit();
				$ok = false;
				echo "<tr>";
				echo "<td>".$personne->nom."</td>";				
				echo "<td>".$personne->prenom."</td>";
				echo "<td>".$personne_rewrited."</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
		if($ok){
			echo "=> pas de problemes";
			return true;
		}
		exit();
	}
	
	public function test_IDUSER_IsUnique(){
		echo "<h2>Test de doublons sur `IDUSER` dans la table ".DB_TABLE_USER."</h2>";
		$ok = true;
		$resultat = ODBCHelper::exec("SELECT IDUSER FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE."");
		if(!$resultat){
			echo "failure request";
		}
		echo "<table>";
	    
	    while($fetch = ODBCHelper::fetch($resultat)) {
			$result_personne = ODBCHelper::exec("SELECT nom, prenom  FROM ".DB_TABLE_USER." WHERE IDUSER LIKE '".$fetch['IDUSER']."' AND ".AFFICHAGE."");
			$fetch_personne_result = ODBCHelper::fetchAll($result_personne);
			if(count($fetch_personne_result) < 2){
				continue;
			}
			$ok = false;
			echo '<tr>';
			foreach ($fetch_personne_result as $key => $nom_prenom) {
				foreach ($nom_prenom as $key => $value) {
					echo "<td>";				
					echo $value;
					echo "</td>";
				}
				break;
			}
			echo "<td>SELECT nom, prenom  FROM ".DB_TABLE_USER." WHERE IDUSER LIKE '".$fetch['IDUSER']."'</td>";
			echo "</tr>";
			
		}
		echo "</table>";

		if($ok){
			echo "=> pas de doublons";
			return true;
		}
		exit();
	}

	public function test_ReconnaissanceFonction(){
		echo "<h2>Test de reconnaissance des professions sur une liste distincte</h2>";
		echo "<h3>liste des personnes dont la fonction n'est pas reconnue</h3>";
		
		$ok = true;
		$liste_fonction = Organization::liste_fonction();
		$liste_personne = Organization::liste_personne();
		
		echo "<table>";
		echo "<tr><th>Nom_Prenom</th><th>Fonction</th></tr>";
		foreach ($liste_personne as $personne) {
			$personne_rewrited = $this->MODELorganization->personne_rewriting($personne->nom, $personne->prenom);
			$recherche = $this->MODELorganization->search_personne_rewrited($personne_rewrited);
			if(!in_array($recherche->info["fonction"], $liste_fonction)){
				$ok = false;
				echo "<tr>";
				echo "<td>".$personne_rewrited."</td>";
				echo "<td>".$recherche->info["fonction"]."</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
		if($ok){
			echo "=> pas de problemes";
			return true;
		} else {
			echo '<h3><span onclick="display_liste_fonctions();" id="view_fonction">[+]</span>Liste des professions</h3>';
			echo '<table style="display:none;" id="liste_fonctions">';
			foreach ($liste_fonction as $value) {
				echo "<tr><td>";
				echo $value;
				echo "</td></tr>";
			}
			echo "</table>";
		}
		exit();
	}

	public function printFonction(){
        $resultat = $this->db->query("SELECT DISTINCT fonction FROM users WHERE  ".AFFICHAGE." ORDER BY fonction");
        $ret = '<select id="liste_fonctions" onchange="changeFonction();">';
        while($fetch_result = $resultat->fetch(PDO::FETCH_ASSOC)) {
            $ret .= '<option value="'.$fetch_result['fonction'].'">'.$fetch_result['fonction'].'</option>';
        }
        $ret .= "</select>";
        return $ret;
    }

    public function testListePersonne(){
		$liste_personne = $this->MODELorganization->liste_personne();
		for($i = 0; $i < count($liste_personne); $i++) {
			$value = str_replace(" ", "-", $liste_personne[$i]['nom']).'_'.str_replace(" ", "-", $liste_personne[$i]['prenom']);
		}	
    }
}
?>
<script type="text/javascript">
var on = false;
function display_liste_fonctions(){
	var str_display = "none"	
	if(!on){
		document.getElementById("view_fonction").innerHTML = "[-]";
		document.getElementById("liste_fonctions").style.display = "block";
	} else {
		document.getElementById("view_fonction").innerHTML = "[+]";
		document.getElementById("liste_fonctions").style.display = "none";		
	}
	on = !on;
}
</script>