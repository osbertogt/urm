<?php
$servername = "localhost";
$username = "datapluscom_urm"; 
$password = "dp$2025Urm"; 
$database = "datapluscom_urm"; 

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");