<?php
// Projet TraceGPS - version web mobile
// fichier : controleurs/CtrlDemarrerEnregistrementParcours.php
// Rôle : préparer la vue de démarrage d'un parcours
// Dernière mise à jour : 5/9/2019 par JM CARTRON

// on vérifie si le demandeur de cette action est bien authentifié
if ( $_SESSION['niveauConnexion'] == 0)
{   // si le demandeur n'est pas authentifié, il s'agit d'une tentative d'accès frauduleux
    // dans ce cas, on provoque une redirection vers la page de connexion
    header ("Location: index.php?action=Deconnecter");
}
else
{   if ( ! isset ($_POST ["txtLatitude"]) && ! isset ($_POST ["txtLongitude"]) && ! isset ($_POST ["txtAltitude"]) && ! isset ($_POST ["btnFrequence"]) )
    {   // si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
        $latitude = '';
        $longitude = '';
        $altitude = '0';
        $frequence = '';
        $message = '';
        $typeMessage = '';			// 2 valeurs possibles : 'information' ou 'avertissement'
        $themeFooter = $themeNormal;
        include_once ('vues/VueDemarrerEnregistrementParcours.php');
    }
    else
    {   // récupération des données postées
        if ( empty ($_POST ["txtLatitude"]) == true)  $latitude = "";  else   $latitude = $_POST ["txtLatitude"];
        if ( empty ($_POST ["txtLongitude"]) == true)  $longitude = "";  else   $longitude = $_POST ["txtLongitude"];
        if ( empty ($_POST ["txtAltitude"]) == true)  $altitude = "0";  else   $altitude = $_POST ["txtAltitude"];
        if ( empty ($_POST ["btnFrequence"]) == true)  $frequence = "";  else   $frequence = $_POST ["btnFrequence"];
        
        if ($latitude == '' || $longitude == '' || $frequence == '')    // l'altitude n'est pas obligatoire
        {   // si les données sont incomplètes, réaffichage de la vue avec un message explicatif
            $message = 'Erreur : données incomplètes.';
            $typeMessage = 'avertissement';
            $themeFooter = $themeProbleme;
            include_once ('vues/VueDemarrerEnregistrementParcours.php');
        }
        else
        {   // connexion du serveur web à la base MySQL
            include_once ('modele/DAO.class.php');
            $dao = new DAO();
            
            // récupération de l'id de l'utilisateur
            $idUtilisateurConsulte = $dao->getUnUtilisateur($pseudo)->getId();
            
            // créer et enregistrer la trace
            $laTrace = new Trace(0, date('Y-m-d H:i:s', time()), null, false, $idUtilisateurConsulte);
            $ok = $dao->creerUneTrace($laTrace);
            // récupération de l'id de la trace
            $idTrace = $laTrace->getId();
            
            // créer et enregistrer le premier point
            $idPoint = 1;
            $dateHeure = date('Y-m-d H:i:s', time());
            $rythmeCardio = 0;
            $tempsCumule = 0;
            $distanceCumulee = 0;
            $vitesse = 0;
            $unPoint = new PointDeTrace($idTrace, $idPoint, $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio, $tempsCumule, $distanceCumulee, $vitesse);
            $ok = $dao->creerUnPointDeTrace($unPoint);
            
            
            
            if(checkboxEnvoyer == 1){
                $adressedestinataire = $dao->getLesUtilisateursAutorises($idUtilisateurConsulte);
                $nomUtilisateurConsulte = $dao->getUnUtilisateur($pseudo)->getPseudo();
                
                
                $from = "delasalle.sio.haupas.d@gmail.com";  
                $to = "$adressedestinataire"; 
                $headers = "From:" . $from;
                $subject = "TraceGPS";  
                $message = "Cher ou ch�re xxxxxxx
    
                            Vous avez demand� � " . $nomUtilisateurConsulte . " l'autorisation de consulter ses parcours." .
                            $nomUtilisateurConsulte . " vient de d�marrer un nouveau parcours � " . time() . "
    
                            Cordialement
                            L'�quipe TraceGPS";  
          
                
                mail($to,$subject,$message, $headers);
            }
            
            unset($dao);		// fermeture de la connexion à MySQL
            
            // on mémorise les paramètres dans des variables de session
            $_SESSION['frequence'] = $frequence;
            $_SESSION['idTrace'] = $idTrace;
            $_SESSION['idPoint'] = $idPoint;
            
            // redirection vers la page d'envoi de la position
            header ("Location: index.php?action=EnvoyerPosition");
        }
    }
}