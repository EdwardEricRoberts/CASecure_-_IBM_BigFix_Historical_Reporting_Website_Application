<?php
try {
    $db = new PDO('pgsql:host=localhost;dbname=CASecure2', 'postgres', 'abc.123');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = $e->getMessage();
}