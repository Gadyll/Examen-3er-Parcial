"No me acuerdo de este"

<?php
session_start();
session_destroy();
header("Location: login.html");
exit();
?>
