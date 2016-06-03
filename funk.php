<?php
//here will the functions used in the application

function connect_db(){
    global $connection;
    $host="localhost";
    $user="test";
    $pass="t3st3r123";
    $db="test";
    $connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
    mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
}

function logi(){

    if (isset($_SESSION['user'])) {
        header("Location: ?page=ideed");
    }
    //kontrollib, kas kasutaja on üritanud juba vormi saata. Kas päring on tehtud POST (vormi esitamisel) või GET (lingilt tulles) meetodil, saab teada serveri infost, mis asub massiivist $_SERVER võtmega 'REQUEST_METHOD'
    if (isset($_SERVER['REQUEST_METHOD'])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            //Kui meetodiks oli POST, kontrollida kas vormiväljad olid täidetud. Vastavalt vajadusele tekitada veateateid (massiiv $errors)
            $errors = array();
            if (empty($_POST['user']) || empty($_POST['pass'])) {
                if(empty($_POST['user'])) {
                    $errors[] = "Kasutajanimi on puudu";
                }
                if(empty($_POST['pass'])) {
                    $errors[] = "Parool on puudu";
                }
            } else {
                //kui kõik väljad olid täidetud, üritada andmebaasitabelist <sinu kasutajanimi/kood/>_kylalised selekteerida külalist, kelle kasutajanimi ja parool on vastavad 
                global $connection;
                $username = mysqli_real_escape_string($connection, $_POST["user"]);
                $passw = mysqli_real_escape_string($connection, $_POST["pass"]);
                $query = "SELECT * FROM mmatson_kasutajad WHERE username='$username' && passw=SHA1('$passw')";
                $result = mysqli_query($connection, $query) or die("midagi läks valesti");
                //Kui selle SELECT päringu tulemuses on vähemalt 1 rida (seda saab teada mysqli_num_rows funktsiooniga) siis lugeda kasutaja sisselogituks -> luua sessiooniväli 'user' ning suunata ta loomaaia vaatesse
                $queryresult = mysqli_fetch_assoc($result);
                $rows = mysqli_num_rows($result);
                    if ( $rows > 0) {
                        $_SESSION['user'] = $username;
                        header("Location: ?page=ideed");
                    }
            }
            include_once 'views/login.html';
        //igasuguste vigade korral ning lehele esmakordselt saabudes kuvatakse kasutajale sisselogimise vorm failist login.html
        } else {
             include_once 'views/login.html';
        }
    }
}

function register() {

    if (isset($_SERVER['REQUEST_METHOD'])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            //Kui meetodiks oli POST, kontrollida kas vormiväljad olid täidetud. Vastavalt vajadusele tekitada veateateid (massiiv $errors)
            $errors = array();
            if (empty($_POST['user']) || empty($_POST['pass']) || empty($_POST['pass2']) ) {
                if(empty($_POST['user'])) {
                    $errors[] = "Sisesta kasutajanimi";
                }
                if(empty($_POST['pass'])) {
                    $errors[] = "Sisesta parool";
                }
                if(empty($_POST['pass2'])) {
                    $errors[] = "Sisesta parool uuesti!";
                }
                if($_POST['pass'] != $_POST['pass2']) {
                    $errors[] = "Paroolid ei kattu, proovi uuesti!";
                }
            } else {

                global $connection;
                $username = htmlspecialchars(mysqli_real_escape_string($connection, $_POST["user"]));
                $passw = htmlspecialchars(mysqli_real_escape_string($connection, $_POST["pass"]));
                $query = "SELECT * FROM mmatson_kasutajad WHERE username='$username'";
                $result = mysqli_query($connection, $query) or die("midagi läks valesti");

                if(mysqli_num_rows($result) < 1) {
                    $query = "INSERT INTO mmatson_kasutajad (username, passw) VALUES (\"$username\", SHA1(\"$passw\"))";
                    print_r($query);
                    $result = mysqli_query($connection, $query) or die("P&auml;ring eba&otilde;nnestus!");
                    if ($result) {
                        // Free result set
                        mysqli_free_result($result);
                        header("Location: ?page=login");
                    }
                } else {
                    $errors[] = "Proovi muud kasutajanime!";
                }
                include_once "views/register.html";
            }
        }
    }

    include_once "views/register.html";
}

function logout(){
    $_SESSION=array();
    session_destroy();
    header("Location: ?page=login");
}


function kuva_ideed() {
    if (!isset($_SESSION['user'])) {
        header("Location: ?page=login");
    }
    global $connection;
    $ideed = [];

    $query= "SELECT mmatson_ideed.id, id_title, mmatson_kasutajad.username AS id_author, id_date, HOUR(id_time) FROM mmatson_ideed INNER JOIN mmatson_kasutajad ON mmatson_ideed.id_author = mmatson_kasutajad.id";
    $result = mysqli_query($connection, $query) or die("$query - ".mysqli_error($connection));
    while ($r=mysqli_fetch_assoc($result)){
//        print_r($r);
        $ideed[$r['id']]= $r;
    }
//    print_r($ideed);
    include_once('views/ideed.html');
}

function lisa() {
    if (!isset($_SESSION['user'])) {
        header("Location: ?page=login");
    }

    if (isset($_SERVER["REQUEST_METHOD"])) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            //check, if form has been filled
            $errors = array();

            if (empty($_POST["title"] || empty($_SESSION["user"])) ) {
                if(empty($_POST["title"])) {
                    $errors[] = "Palun t&auml;ida v&auml;ljad!";
//                    print_r($error);
                }
            } else {
                global $connection;
                $username = mysqli_real_escape_string($connection, $_SESSION["user"]);
                $query = "SELECT id FROM mmatson_kasutajad WHERE username='$username'";
                $result = mysqli_query($connection, $query) or die("P&auml;ring eba&otilde;nnestus!");
                //Kui selle SELECT päringu tulemuses on vähemalt 1 rida (seda saab teada mysqli_num_rows funktsiooniga) siis lugeda kasutaja sisselogituks -> luua sessiooniväli 'user' ning suunata ta loomaaia vaatesse
                $id = mysqli_fetch_assoc($result);
                    // if user exists, allow him/her to add ideas
                    if ( mysqli_num_rows($result) > 0) {
                        $title = htmlspecialchars(mysqli_real_escape_string($connection, $_POST["title"]));
                        $author = htmlspecialchars(mysqli_real_escape_string($connection, $id["id"]));
                        $query = "INSERT INTO mmatson_ideed (id_title, id_date, id_time, id_author) VALUES (\"$title\", now(), now(), $author)";
                        $result = mysqli_query($connection, $query) or die("P&auml;ring eba&otilde;nnestus!");
                        // if insert was successful, redirect customer to idea list page
                        if ($result > 0) {
                            // Free result set
                            mysqli_free_result($result);
                            // if insert was successful, redirect to ideed page
                            header("Location: ?page=ideed");
                        } else {
                            $errors[] = "P&auml;ring eba&otilde;nnestus!";
                        }
                    } else {
                        $errors[] = "P&auml;ring eba&otilde;nnestus! No rigths!";
                    }
                    //include_once('views/ideed.html');
            }
            kuva_ideed();
        }
    }
}

?>
