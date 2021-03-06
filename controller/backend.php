<?php

require_once('model/CommentManager.php');
require_once('model/MemberManager.php');
require_once('model/PostManager.php');

try {
    
    //**************************************************************************************
    // Controller backend PostManager (+backend CommentManager) (+Controller frontend PostManager)          
    //**************************************************************************************

    function postExtract($text) {
        $max=200;
        if (strlen($text) > $max) { // vérifie que texte plus long que max extrait
            // récupère 1er espace après $max pour éviter de couper un mot en plein milieu
            $space = strpos($text,' ',$max);
            //récupère l'extrait jusqu'à l'espace préalablement cherché auquel on ajoute "..."
            $postExtract = substr($text,0,$space).'...';
        } else {
            $postExtract = $text;
        }
        return $postExtract;
    }

    function newPost($postTitle, $postContentHTML, $postContentText, $postBefore) {
        if ($postTitle == "" || $postContentHTML == "") {
            $message_error =  'Erreur : Veuillez renseigner tous les champs';
            listPosts(1, $message_success, $message_error);
        } else {
            $postExtract = postExtract($postContentText);
            $postManager = new \FredLab\tp4_blog_ecrivain\Model\PostManager();
            if(!empty($postBefore)) {
                $postManager->addPost($postTitle, $postContentHTML, $postExtract, $postBefore); 
            } else {
                $postManager->addPost($postTitle, $postContentHTML, $postExtract, "");     
            }
            $message_success =  'Votre billet "' . $postTitle . '" a bien été publié !';
            listPosts(1, $message_success, "");
        }
    }
    
    function modifPost($postId, $newPostTitle, $newPostContentHTML, $newPostContentText) {
        $postManager = new \FredLab\tp4_blog_ecrivain\Model\PostManager();
        $postManager->changePostTitle($postId, $newPostTitle);
        $postExtract = postExtract($newPostContentText);
        $postManager->changePostContent($postId, $newPostContentHTML, $postExtract);
        $message_success =  'L\'épisode a bien été modifié ci-dessous !';
        post($postId, $message_success, "");        
    }

    function postErase($postId) {
        $postManager = new \FredLab\tp4_blog_ecrivain\Model\PostManager();
        $postManager->deletePost($postId);     
        $commentManager = new \FredLab\tp4_blog_ecrivain\Model\CommentManager();
        $commentManager->deleteComments($postId);     
        $message_success =  'Ce billet et ses commentaires ont bien été supprimés !';
        listPosts(1, $message_success, "");
    }

    //**************************************************************************************
    //           Controller CommentManager (+Controller frontend PostManager)                  
    //**************************************************************************************

    function addCommentRequest($postId, $member, $newComment) {
        $commentManager = new \FredLab\tp4_blog_ecrivain\Model\CommentManager();
        $addCommentRight = $commentManager->getMemberNoComment($member);
        $message_error = "";
        if($addCommentRight['block_comment'] == 1) {
            $message_error =  'Désolé vous n\'êtes pas autorisé à poster des comments';
        } else if($newComment == "") {
            $message_error =  'Désolé votre message est vide';
        } else {
            $commentManager->addComment($postId, $member, $newComment);     
            $message_success =  'Votre commentaire a bien été publié ci-dessous';
        }
        post($postId, $message_success, $message_error);
    }
    
    function modifCommentRequest($postId, $member, $commentId, $modifComment) {
        $commentManager = new \FredLab\tp4_blog_ecrivain\Model\CommentManager();
        $addCommentRight = $commentManager->getMemberNoComment($member);
        $message_error = "";
        if($addCommentRight['block_comment'] == 1) {
            $message_error =  'Désolé vous n\'êtes pas autorisé à poster des comments';
        } else if ($modifComment == "") {
            $message_error =  'Désolé votre message est vide';
        } else {
            $commentManager->replaceComment($commentId, $modifComment);     
            $message_success =  'Votre commentaire a bien été modifié et publié ci-dessous';
        }
        post($postId, $message_success, $message_error);
    }

    function commentSignal($postId, $commentId, $signalId, $member) {
        $commentManager = new \FredLab\tp4_blog_ecrivain\Model\CommentManager();
        $commentManager->signalComment($commentId, $signalId, $member);     
        if ($signalId == 1) {
            $message_error =  'Ce commentaire a bien été signalé à l\'administrateur!';
        } else {
            $message_success =  'Ce commentaire ne sera plus signalé à l\'administrateur!';
        }
        post($postId, $message_success, $message_error);
    }
    
    function commentErase($postId, $commentId) {
        $commentManager = new \FredLab\tp4_blog_ecrivain\Model\CommentManager();
        $commentManager->deleteComment($commentId); 
        if ($postId != "") {
            $message_success =  'Ce commentaire a bien été Supprimé !';
            post($postId, $message_success, "");
        } else {
            $message_success =  'Ce commentaire a bien été Supprimé !';
            listPosts(1, $message_success, "");
        }
    }

    //**************************************************************************************
    //         Controller backend MemberManager (+Controller frontend PostManager)              
    //**************************************************************************************

    function memberBloqComment($memberId, $blockId, $template) {
        $memberManager = new \FredLab\tp4_blog_ecrivain\Model\MemberManager();
        $memberManager->changeMemberNoComment($memberId, $blockId);
        if ($blockId == 1) {
            $message_success =  'Le membre a bien été bloqué et ne pourra plus commenter !';
        } else {
            $message_success =  'Le membre a bien été débloqué et pourra de nouveau commenter !';
        }
        memberDetail($message_success, "", $memberId, $template);
    }

    //**************************************************************************************
    //         Controller backend MemberManager (+Controller frontend PostManager)              
    //**************************************************************************************

    function memberModerator($memberId, $moderatorId, $template) {
        $memberManager = new \FredLab\tp4_blog_ecrivain\Model\MemberManager();
        $memberManager->changeMemberGroup($memberId, $moderatorId);
        if ($moderatorId == 2) {
            $message_success =  'Ce membre a bien été passé en modérateur !';
        } else {
            $message_success =  'Ce membre n\'aura plus le statut de modérateur !';
        }
        memberDetail($message_success, "", $memberId, $template);
    }

//**************************************************************************************
//                   Redirection des erreurs vers page errorView             
//**************************************************************************************

} catch(Exception $e) {
    $errorMessage = $e->getMessage();
    require('view/errorView.php');
}
