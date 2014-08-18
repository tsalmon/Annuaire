<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8"/>
        <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
        <title>Annuaire ADPI</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--[if lt IE 9]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <link rel="stylesheet" href="<?php echo URL; ?>css/knacss.css" media="all">
        <?php
            if(!$toutvoir && !$hierarchie){
                echo '<link rel="stylesheet" href="'.URL.'css/styles.css" media="all">';
            } else {
                echo '<link rel="stylesheet" href="'.URL.'css/style_full.css" media="all">';                
            }
        ?>
        <link rel="stylesheet" href="<?php echo URL; ?>css/style_autocomplete.css" media="all">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
        <script>
        $(function() {

            var accentMap = {
                  "á": "a",
                  "ö": "o",
                  "-": " ",
                  "é": "e",
                  "â": "a",
                  "ô": "o"
                };
            var normalize = function( term ) {
              var ret = "";
              for ( var i = 0; i < term.length; i++ ) {
                ret += accentMap[ term.charAt(i) ] || term.charAt(i);
              }
              return ret;
            };


            var noms = [
                <?php
                if(!DEBUG_MODE){
                    echo $this->displayListePersonne($this->liste_personne);
                }
                ?>
            ];
            $( "#noms" ).autocomplete({
                minLength: 0,
                source: function(request, response) {
                    var matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
                    var results = $.ui.autocomplete.filter(noms, request.term);
                    //response(results.slice(0, 5));
                    response( $.grep( noms, function( value ) {
                      value = value.label || value.value || value;
                      return matcher.test( value ) || matcher.test( normalize( value ) );
                    }));
                },
                focus: function( event, ui ) {
                    if(ui.item === null){
                        return false;
                    }
                    $( "#noms" ).val( ui.item.value.substring(0, ui.item.value.lastIndexOf(" ")));
                    return false;
                },
                select: function( event, ui ) {
                    if(ui.item === null){
                        return false;
                    }
                    var nom_prenom_toString = ui.item.value.substring(0, ui.item.value.lastIndexOf(" ")); 
                    nom_prenom_toString = nom_prenom_toString.replace("_", " "); 
                    $( "#noms" ).val( nom_prenom_toString);
                    $(this).closest("form").submit();
                },
                open: function (event, ul) {
                     $('.ui-autocomplete').append('<li class="show_all"><b><a href="index.php?p='+ $("#noms").val() +'">Voir tous les résultats</b></a></li>'); //See all results
                }
            }).autocomplete( "instance" )._renderItem = function( ul, item ) {
               var  nom_prenom_toString = item.value.substring(0, item.value.lastIndexOf(" "));
                    nom_prenom_toString = nom_prenom_toString.replace("-", " "); 
                    nom_prenom_toString = nom_prenom_toString.replace("_", " "); 
                return $( "<li>" )
                    .append( "<a>" + nom_prenom_toString + "</a>" )
                    .appendTo( ul );
            };
        });
        </script>
    </head>
    <body>
