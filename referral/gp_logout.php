<?php
session_start();
session_destroy();
header("Location: gp_login.php");
exit();
?>