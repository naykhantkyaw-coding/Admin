<?php
session_start();

$host = 'localhost';
$dbname = 'moviebookingsystem';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
//$connection = mysqli_connect('localhost','root','','moviebookingsystem');
?>