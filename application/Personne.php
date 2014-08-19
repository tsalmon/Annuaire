<?php
require_once("ODBCHelper.php");
class Personne{
    public $info; //array assoc

    public function __construct($nom, $prenom, $need_info = false) {
        $this->prenom = $prenom;
        $this->nom = $nom;

        if($need_info){
            $this->getInformations();  
        }
    }

    public function printNom_Prenom($nom, $prenom){
        $nom = trim(suppr_accents($nom));
        $prenom = trim(suppr_accents($prenom));
        return (str_replace(" ", "-", $nom)."_".str_replace(" ", "-", $prenom));
    }

    public function printFonction(){
        $resultat = ODBCHelper::exec("SELECT DISTINCT fonction FROM ".DB_TABLE_USER." WHERE ".AFFICHAGE." ORDER BY fonction");
        $ret = '<select id="liste_fonctions" onclick="changeFonction();">';
        while($fetch_result = ODBCHelper::fetch($resultat)) {
            $sel = "";
            if(strtolower(trim(suppr_accents($this->info["fonction"]))) == strtolower(trim(suppr_accents($fetch_result['fonction'])))){
                $sel = 'selected="selected"';
            } 
            $value = $fetch_result['fonction'];
            $value = str_replace(" de ", "_", $value);
            $value = str_replace(" d ", "_", $value);
            $value = str_replace("-", "_", $value);
            $value = suppr_accents($value);
            $ret .= '<option value="'.str_replace(" ", "_", $value).'" '.$sel.'>'.$fetch_result['fonction'].'</option>';
        }
        $ret .= "</select>";
        return $ret;
    }

    public function printPersonne(){
        if(!$this->info){
            return false;
        }
        $ret =  '<table><col width="130">';        
        foreach ($this->info as $key => $value) {
            if($key ==  "IDUSER"){
                continue;
            }
            $ret .= '<tr><td>'.ucfirst($key).'</td><td>';
            if($key == "nom") {
                $ret .= '<a href="index.php?p='.str_replace(" ", "-", suppr_accents($this->info["nom"])).'_'.str_replace(" ", "-", suppr_accents($this->info["prenom"])).'">'.$value.'</a>';
            } elseif($key == "mail") {
                $ret .= '<a href="mailto:'.$value.'">'.$value.'</a>';
            } else {
                $ret .= $value;
            }
            $ret .= "</td></tr>";
        }
        $ret .= "</table>";
        return $ret;
    }

    public function getChef(){
        $chef_aux = $this->info["responsable"];
        if(strlen($chef_aux) == 0){ //no boss
            return null;
        }
        $chef_aux = explode(" ", $chef_aux);
        $resultat2 = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE (IDUSER LIKE '".$chef_aux[0]." ".$chef_aux[1]."' OR (nom LIKE '".$chef_aux[1]."' AND prenom LIKE '".$chef_aux[0]."')) AND ".AFFICHAGE."");
        if(!$resultat2){
            return null;
        } 
        $chef = ODBCHelper::fetch($resultat2);
        return $chef;
    }

    public function getEquipe(){
        $resultat = ODBCHelper::exec("SELECT nom, prenom FROM ".DB_TABLE_USER." WHERE responsable LIKE '".$this->info['IDUSER']."' AND ".AFFICHAGE."");
        if(!$resultat){
            echo "404 - Cette personne n'existe pas";
            return ;
        } 
        return ODBCHelper::fetchAll($resultat);        
    }

    public function printHierarchy(){
        if(!($this->info)){
            return;
        } 
        $equipe = $this->getEquipe();
        $chef = $this->getChef();
        $ret = '<ul class="root">';
            if($chef){
                $ret .= '<li><a href="index.php?p='.$this->printNom_Prenom($chef["nom"], $chef["prenom"]).'">'.$chef["nom"].' '.$chef["prenom"].'</a></li>';
            } else {
                $ret .= "<li>$chef</li>";
            } 
                $ret .= "<li>";           
                    $ret .= '<ul class="tree">';
                        $ret .= "<li>".$this->nom." ".$this->prenom."</li>";
                        //$ret .= "<li>";
                        if($equipe){
                            $ret .= "<ul>";
                            for ($i = 0; $i < count($equipe); $i++) {
                                $last = "";   
                                if($i == count($equipe) - 1){
                                    $last = 'class="last"';
                                }
                                $ret .= '<li '.$last.'><a href="index.php?p='.$this->printNom_Prenom($equipe[$i]["nom"], $equipe[$i]["prenom"]).'">'.$equipe[$i]["nom"]." ".$equipe[$i]["prenom"].'</a></li>';
                            }
                            $ret .= "</ul>";
                        //$ret .= "</li>";
                    $ret .= "</ul>";
                        }
                $ret .= "</li>";
        $ret .= "</ul>";
        //$ret .= "</table>";

        return $ret;
    }

    /*
     * don't use str_replace() because of multiple whitespaces "foo    bar"
     */
    public function get_p_SQL($nom){
        $nom_compose = str_replace("-", " ", $nom);
        $nom_compose = explode(" ", $nom_compose);
        $ret_nom = $nom_compose[0];
        for($i = 1; $i < count($nom_compose); $i++){
            $ret_nom .= "%".$nom_compose[$i];
        }
        return $ret_nom;
    }

    public function getInformations(){
        $nom_compose = suppr_accents($this->get_p_SQL($this->nom));     
        $prenom_compose = suppr_accents($this->get_p_SQL($this->prenom));

        $resultat = ODBCHelper::exec("SELECT IDUSER, nom, prenom, fonction, mail, telephone, portable, salle, uo, responsable FROM ".DB_TABLE_USER." WHERE nom LIKE '".$nom_compose."' COLLATE Latin1_general_CI_AI AND prenom LIKE '".$prenom_compose."' COLLATE Latin1_general_CI_AI AND ".AFFICHAGE."");
        // COLLATE Latin1_general_CI_AI => can match café, cafe, câfé, ...

        if(!$resultat){
            echo "404 - Cette personne n'existe pas";
            return ;
        } 
        $this->info = ODBCHelper::fetch($resultat);

        //$this->info["fonction"] = mb_convert_case($this->info["fonction"], MB_CASE_LOWER, "UTF-8");
        if($this->info) {
            if(isset($this->info["uo"])){
                $this->info["departement"] = $this->info["uo"];
                unset($this->info["uo"]);            
            } else {
                $this->info["departement"] = null;                
            }
            unset($this->info["uo"]);            
        }
    }
}
?>
