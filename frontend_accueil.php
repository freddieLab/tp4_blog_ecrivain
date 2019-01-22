<?php session_start(); ?>

<?php $title = 'Accueil'; ?>
<?php $template = 'frontend'; ?>
<?php ob_start(); ?>

<br />
<p>===========================================================</p>
<!-- Confirm connect -->

<h3>
    Bienvenue
    <?php 
        echo $_SESSION['first_name'] . ' !';
    ?>
</h3>

<p>===========================================================</p>
<!-- Liste des posts -->

<ul>
    <?php   // connexion à la base de données 
            try { 
                $db = new PDO('mysql:host=localhost;dbname=forteroche', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            }
            catch(Exception $e) {
                die('Erreur : '.$e->getMessage());
            }
                // Liste des posts par ordre décroissant 
        
            $req = $db->query('SELECT COUNT(id) AS nbre_posts FROM posts');
            $data = $req->fetch();
            echo '<h3>' . 'Liste des ' . $data['nbre_posts'] . ' posts publiés à ce jour :' . '</h3>';
            $req->closeCursor(); // Termine le traitement de la requête
            $compteur = $data['nbre_posts'];
            $req = $db->query('SELECT chapter_title, creation_date FROM posts ORDER BY creation_date DESC');
            while ($data = $req->fetch()) {
            echo '<li style="color: red;">' . ' N° ' . $compteur . ' : ' . $data['chapter_title'] . '</li>';
            $compteur--;
            }
            $req->closeCursor(); // Termine le traitement de la requête
        ?>
</ul>

<p>===========================================================</p>
<!-- Les détails par groupe de 5 posts -->

<p>Page
    <?php // Boucle affichant le bon nombre de liens vers d'autres pages, par groupe de 5 posts 
        
            $req = $db->query('SELECT COUNT(id) AS nb_posts FROM posts');
            $data = $req->fetch();
            for ($page=1, $pages_max=($data['nb_posts']/5)+($data['nb_posts']%5); $page<=$pages_max; $page++) {
                echo '<a href="frontend_accueil.php?page=' . $page .'">' . $page . '</a>' . ' '; // page sélectionnée envoyé en url GET
                $req->closeCursor(); // Termine le traitement de la requête
            }
        ?>
</p>

<?php    // Récupération des indices de posts max et min de chaque page 
    
        if ($_GET['page']) {
            $id_max_page = ($_GET['page']-1)*5;  
            $billet_max = $data['nb_posts']-(($_GET['page']-1)*5);
        } else {
            $id_max_page = 0;
            $billet_max = $data['nb_posts'];
        }
        if ($billet_max <= 5) {
            $billet_min = 1;
        } else {
            $billet_min = $billet_max-4;
        }
            // On affiche 5 posts max par page et on les définis en paramètrant le OFFSET à opérer 
    
        $req = $db->prepare('SELECT id, chapter_title, chapter_content, DATE_FORMAT(creation_date, \'%d/%m/%Y à %Hh%imin%ss\') AS date FROM posts ORDER BY creation_date DESC LIMIT 5 OFFSET :idmax');
        $req->bindValue(':idmax', $id_max_page, PDO::PARAM_INT);
        $req->execute(); //
        if ($billet_max == $data['nb_posts']) {
            echo '<h3>' . 'Les 5 derniers posts du n° ' . $billet_max . ' au n° ' . $billet_min . '</h3>';
        } else {
        echo '<h3>' . 'posts du n° ' . $billet_max . ' au n° ' . $billet_min . '</h3>';
        };   
            // On affiche le détail de chaque billet de la plage sélectionnée
    
        while ($data = $req->fetch()) {
        echo '<h3 class="news">' . $data['chapter_title'] . ' : ' . ' le '. $data['date'] . '</h3>';
        echo '<p>' . htmlspecialchars($data['chapter_content']) . '</p>';
            
            // on compte le nbre de comments et l'affiche le cas échéant
        $req1 = $db->prepare('SELECT COUNT(post_id) AS nbre_comment FROM comments WHERE post_id = ?');
        $req1->execute(array($data['id'])); 
        $data1 = $req1->fetch(); 
            
            // lien vers page des comments avec id du billet envoyé en url GET
        echo '<a href="frontend_comment_billet.php?billet=' . $data['id'] .'">' . 'Commentaires (' . $data1['nbre_comment'] . ')' . '</a>' . '<br>';
        $req1->closeCursor(); // Termine le traitement de la requête 1
        }
        $req->closeCursor(); // Termine le traitement de la requête
    ?>

<?php $content = ob_get_clean(); ?>

<?php require('template.php'); ?>
