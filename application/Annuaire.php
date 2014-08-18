<?php
class Annuaire extends Controller{	
	public $MODELorganization = null;

	function __construct(){
		parent::__construct();
		$this->index();
    }

	function index(){
	 	$personne = null;
	 	$hierarchie = null;
	 	$toutvoir = null;

		$this->MODELorganization = $this->loadModel('Organization');
		$this->liste_personne = $this->MODELorganization->liste_personne();
		
		if(isset($_GET["organisation"])){
			$hierarchie = $this->MODELorganization->makePersonne(array("nom" => "Sauve", "prenom" => "Guillaume", "IDUSER" => "SAUVE Guillaume"), null);
		} elseif (isset($_GET["toutvoir"])) {
			$toutvoir = $this->MODELorganization->toutVoir();
		} 
		else {
			if(isset($_GET['p'])){
				$personne = $this->getPersonne($_GET['p']);
			}
			if(is_array($personne) && count($personne) == 1){
			    header("location: ./index.php?p=".str_replace(" ", "-", $personne[0]->nom)."_".str_replace(" ", "-", $personne[0]->prenom));
			}

			if(isset($_GET['p']) && $personne == null){
				$personne = "Personne non trouvé";
			}
		}
	
		require 'Application/_templates/header.php';
   		require 'view.php';
    	require 'Application/_templates/footer.php';
	}

	public function searchPersonne($nom, $prenom){
		foreach ($this->liste_personne as $key => $value) {
			if(str_replace("-", " ", $value->nom) == $nom && str_replace("-", " ", $value->prenom) == $prenom){
				return new Personne($nom, $prenom, true);
			}
			if(str_replace("-", " ", $value->nom) == $prenom && str_replace("-", " ", $value->prenom) == $nom){
				return new Personne($prenom, $nom, true);
			}
		}
		return null;
	}


	public function getPersonne($pattern){
		$personne = null;
		$handler_personne = str_replace("-", " ", $pattern);
		$handler_personne = explode("_", $handler_personne);
		for ($i=0; $i < count($handler_personne); $i++) { 
			$handler_personne[$i] = ucfirst(strtolower($handler_personne[$i]));
		} 
		if(count($handler_personne) != 2){
			return $this->deepSearchPersonne($handler_personne);
		}
		return $this->searchPersonne($handler_personne[0], $handler_personne[1]);	
	}
	
	public function deepSearchPersonne($handler_personne){
		$result = array();
		if(count($handler_personne) == 1){
			$result = $this->MODELorganization->searchPersonneByAttr(array("nom" => $handler_personne[0]));
			$result+= $this->MODELorganization->searchPersonneByAttr(array("prenom" => $handler_personne[0]));
		} else {
			for ($i=1; $i < count($handler_personne); $i++) { 
				$search_nom    = ucfirst(strtolower(implode(" ", array_slice($handler_personne, 0, $i))));
				$search_prenom = ucfirst(strtolower(implode(" ", array_slice($handler_personne, $i))));
				$result_search = $this->searchPersonne($search_nom, $search_prenom);
				if($result_search){
					array_push($result, $result_search);
				}
			}
		}
		return $result;
	}

	public function displayPrenom($prenom){
		$prenom_explode = explode(" ", $prenom);
		for ($i=0; $i < count($prenom); $i++) { 			
			if(isset($prenom_explode[$i][0])){
				$prenom_explode[$i] = $prenom_explode[$i][0];
			} else {
			}
		}
		return implode("-", $prenom_explode);;
	}

	public function displayListePersonne($liste_personne){
		$ret_display_listePersonne = "";
		for($i = 0; $i < count($liste_personne); $i++) {
			$val_nom = str_replace(" ", "-", suppr_accents(trim($liste_personne[$i]->nom)));
        	$val_prenom = str_replace(" ", "-", suppr_accents(trim($liste_personne[$i]->prenom)));
			$ret_display_listePersonne .= '{ key: '.$i.', nom_prenom: "'.$liste_personne[$i]->nom.' '.$liste_personne[$i]->prenom.'", value:"'.$val_nom.' '.$val_prenom.' '.$val_nom.'"},';
  		}
		return rtrim($ret_display_listePersonne, ",");
	}

	public function loadModel($model_name){
        require_once $model_name.'.php';
        return new $model_name($this->db);    
    }
}
?>