<header id="header" role="banner" class="line pam">
    <a id="title" href="index.php">Annuaire ADPI</a> 
    <a href="index.php?organisation=show">Organisation</a> 
    <a href="index.php?toutvoir=show">Tout voir</a> 
</header>
    <aside class="mod left w20 mrs pam aside">
        <nav id="navigation" role="navigation">
            <?php
            if($personne){
                echo '<form class="ui-widget" action="index.php" method="GET">
                <input id="noms" name="nom" placeholder="Recherche...">
                <input type="submit"/>
                </form>';  
            }
            ?>
        </nav>
    </aside>
    <aside class="pam mod right w20 mls aside">
        <?php
            if($personne && is_a($personne, 'Personne')) {
                if(file_exists(PHOTO."".strtoupper($personne->nom."".$personne->prenom[0]).".jpg" )){
                    echo '<img src="img/photo/'.strtoupper($personne->nom."".$personne->prenom[0]).'.jpg" alt="">';                    
                } else {
                    echo '<img src="img/photo/placeholder.png" alt="">';
                }
                echo "<hr/>";
                echo ($personne->printHierarchy()); 
            } 
            if($hierarchie){
                echo "hierarchie";
            }
        ?>
    </aside>
    <div id="main" role="main" class="mod pam">
        <?php 
            if($personne && is_a($personne, 'Personne')){
                echo $personne->printPersonne(); 
            } 
            elseif (is_array($personne) && count($personne) > 0) {
                foreach ($personne as $key => $value) {
                    echo $value->printPersonne();
                }
            } elseif ($toutvoir) {
                echo "<table>";
                echo "<tr>";
                foreach ($toutvoir[0] as $key => $value) {
                    echo "<th>".$key."</th>";
                }           
                echo "</tr>"; 

                foreach ($toutvoir as $key => $value) {
                    echo "<tr>";
                    foreach ($toutvoir[$key] as $key => $value) {
                        echo "<td>";
                        echo $value;
                        echo "</td>";
                    }           
                    echo "</tr>";
                }           

                echo "</table>";
            } elseif ($hierarchie){
                $this->MODELorganization->explore($hierarchie);
            } elseif(!$personne && !$toutvoir && !$hierarchie) {
               echo '<form class="ui-widget content_accueil"  action="index.php" method="GET">
                    <label class="accueil" for="noms">Cherchez une personne: </label><input class="accueil" id="noms" name="nom" placeholder="Recherche...">
                <input type="submit"/>
                </form>';                
            } else {
                echo "<p>Personne introuvable</p>";
            }
        ?>        
    </div>

<footer id="footer" role="contentinfo" class="line pam txtcenter">
</footer>