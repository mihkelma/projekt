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

    if (isset($_POST['user'])) {
        header("Location: ?page=ideed");
    }
    //kontrollib, kas kasutaja on üritanud juba vormi saata. Kas päring on tehtud POST (vormi esitamisel) või GET (lingilt tulles) meetodil, saab teada serverii infost, mis asub massiivist $_SERVER võtmega 'REQUEST_METHOD'
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
                //kui kõik väljad olid täidetud, üritada andmebaasitabelist <sinu kasutajanimi/kood/>_kylalised selekteerida külalist, kelle kasutajanimi ja  parool on vastavad
                global $connection;
                $username = mysqli_real_escape_string($connection, $_POST["user"]);
                $passw = mysqli_real_escape_string($connection, $_POST["pass"]);
                $query = "SELECT * FROM mmatson_kasutajad WHERE username='$username' && passw=SHA1('$passw')";
                $result = mysqli_query($connection, $query) or die("midagi läks valesti");
                //Kui selle SELECT päringu tulemuses on vähemalt 1 rida (seda saab teada mysqli_num_rows funktsiooniga) siis lugeda kasutaja sisselogituks -> luuua sessiooniväli 'user' ning suunata ta loomaaia vaatesse
                $queryresult = mysqli_fetch_assoc($result);
                $rows = mysqli_num_rows($result);
                    if ( $rows > 0) {
                        $_SESSION['user'] = $username;

                    }
            }
        //igasuguste vigade korral ning lehele esmakordselt saabudes kuvatakse kasutajale sisselogimise vorm failist login.html
        } else {
             include_once 'views/login.html';
        }
    }

}
function logout(){
    $_SESSION=array();
    session_destroy();
    header("Location: ?");
}

function kuva_ideed() {
    global $connection;
    $ideed = [];

    $query= "SELECT id, id_title, id_author, id_date, HOUR(id_time) FROM mmatson_ideed";
    $result = mysqli_query($connection, $query) or die("$query - ".mysqli_error($connection));
    while ($r=mysqli_fetch_assoc($result)){
//        print_r($r);
        $ideed[$r['id']]= $r;
    }
//    print_r($ideed);
    include_once('views/ideed.html');
}


?>