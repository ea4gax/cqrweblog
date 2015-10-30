<?php
include("config_defaults.php");
include("config.php");
include("inc/header.php");
include("inc/parse_edit.php");
?>

<html>
<head>
<?php 
echo '<title>' . strtoupper(logid_to_call($log_id)) . ' \'s Logbook</title>';
include("inc/metaheader.php");
?>
<meta charset="UTF-8">
</head>
<body style="overflow:auto;">
<?php
echo '<div id="head">' . "\n";
echo '<h1 align="center">Logbook of ' . strtoupper(logid_to_call($log_id)) . '</h1><br /><br />';
echo '</div>' . "\n";
include ("inc/edit_input.php");
?>

<?php 
include("inc/edit_proc.php");
echo '<p align="right">' . count_qsos( $log_id ) . ' QSO in Log</p>' . "\n";
?>

</body>
<?php 
include("inc/metafooter.php");
?>
</html>
