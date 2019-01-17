<?php 

    session_start(); // On démarre la session AVANT toute chose

    // connexion à la base de données   

    try { // connexion à la base de données 
        $db = new PDO('mysql:host=localhost;dbname=forteroche', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
    catch(Exception $e) {
        die('Erreur : '.$e->getMessage());
    }
    
    if ($_GET['delete']) {
        $req = $db->prepare('DELETE FROM contacts WHERE pseudo = :monpseudo');
            $req->execute(array(
                'monpseudo' => $_SESSION['pseudo']
            ));  
        $req->closeCursor(); // Termine le traitement de la requête
        header('Location: frontend_admin_compte.php');
    }

    if ($_GET['nv_email']) {
        $req = $db->prepare('UPDATE contacts SET email = :nvemail WHERE pseudo = :monpseudo');
        $req->execute(array(
            'nvemail' => $_GET['nv_email'],
            'monpseudo' => $_SESSION['pseudo']
        ));
        $req->closeCursor(); // Termine le traitement de la requête
        header('Location: frontend_admin_compte.php');
    } 
    
    else if ($_GET['nv_mp']) {
        $req = $db->prepare('UPDATE contacts SET password = :newpassword WHERE pseudo = :monpseudo');
        $req->execute(array(
            'newpassword' => $_GET['nv_mp'],
            'monpseudo' => $_SESSION['pseudo'] 
        ));
        $req->closeCursor(); // Termine le traitement de la requête
        header('Location: frontend_admin_compte.php');
    }
?>

<!--****************************************************************************************************************-->
<!--                                                  HTML                                                          -->
<!--****************************************************************************************************************-->

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Jean Forteroche</title>
    <!-- Feuille de style css et Bibliothèque d'icones FontAwesome -->
    <link rel="stylesheet" href="frontend_style.css" />
</head>

<body>

    <!-- Footer -->
    <br />
    <p>===========================================================</p>
    <?php include("forteroche_footer.php"); ?>

</body>

</html>
