<?php
$host="localhost"; $usuario="root"; $clave=""; $bd="prueba2";
$conn = new mysqli($host,$usuario,$clave,$bd);
if($conn->connect_error) die("Error: ".$conn->connect_error);

$sql="SELECT * FROM pasteles";
$res=$conn->query($sql);
$pasteles=[];
if($res && $res->num_rows>0){
    while($row=$res->fetch_assoc()){
        $pasteles[]=$row;
    }
}
header('Content-Type: application/json');
echo json_encode($pasteles);
$conn->close();
?>
