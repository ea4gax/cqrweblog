<?php
include("inc/header.php");
include("inc/parse_log.php");
?>

<html>
<head>
<?php 
echo '<title>' . strtoupper(logid_to_call($log_id)) . ' \'s Logbook</title>';
include("inc/copy.php");
?>
<link rel="stylesheet" type="text/css" href="inc/log.css">
<meta charset="UTF-8">
</head>
<body>

<?php
echo '<h1 align="center">Logbook of ' . strtoupper(logid_to_call($log_id)) . '</h1><br /><br />';
include ("inc/log_input.php");
?>
<br /><br />

<?php 
include("inc/log_proc.php");
echo '<p align="right">' . count_qsos( $log_id ) . ' QSO in Log</p>' . "\n";
?>
<hr>
<br><br>

<div id="main_wrap">
<div id="sidebar">
<?php include("inc/sidebar.php");?>
</div>
<div id="content">

<?php
//include("inc/log_proc.php");
?>
<?php include("inc/qsotable.php");?>

</div>
</div>
</body>
</html>