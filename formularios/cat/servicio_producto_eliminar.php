<?php
include '../../dbc.php';

$id = intval($_POST['id']);
mysqli_query($conn, "DELETE FROM tbl_productos_servicios WHERE id = $id");
