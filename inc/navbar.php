<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="
			<?php echo $cqrweblog_root . '/'  ?>
			">Index</a>
    </div>
    <div>
      <ul class="nav navbar-nav">
        <li 
					<?php if (isset($logactive)) { echo 'class="active"';}?>
				><a href="
					<?php echo $cqrweblog_root.'log2.php?log_id=' . $log_id ?>
				">Log</a></li>
        <li
					<?php if (isset($searchactive)) { echo 'class="active"';}?>
				><a href="
				<?php echo $cqrweblog_root.'logsearch2.php?log_id=' . $log_id ?>
				">Search</a></li>
        <li
					<?php if (isset($statsactive)) { echo 'class="active"';}?>
				><a href="
				<?php
				if ($altstats[$log_id]) {
						echo $cqrweblog_root.'stats2.php?log_id=' . $log_id ;
				}
				else {
						echo $cqrweblog_root.'statsnew.php?log_id=' . $log_id ;
				}

				?>
				">Statistics</a></li>
        <li><a href="
				<?php echo $cqrweblog_root.'publog.php?log_id=' . $log_id ; ?>
				">Public</a></li>
      </ul>
    </div>
  </div>
</nav>