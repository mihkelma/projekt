<?php
require_once('funk.php');
session_start();
connect_db();

$page="pealeht";
if (isset($_GET['page']) && $_GET['page']!=""){
        $page=htmlspecialchars($_GET['page']);
}

include_once('views/header.html');

switch($page){
        case "login":
                logi();
        break;
        case "ideed":
                kuva_ideed();
        break;
        case "logout":
                logout();
        break;
        case "lisa":
                lisa();
        break;
        default:
                include_once('views/content.html');
        break;
}

include_once('views/footer.html');

?>