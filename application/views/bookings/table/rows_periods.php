<td align="right" width="100" style="padding:15px 5px;">
	<strong><?php echo html_escape($name) ?></strong><br />
	<span style="font-size:90%">
		<?php
		$time_fmt = setting('time_format_period');
		if (strlen($time_fmt)) {
			$start = date($time_fmt, strtotime($time_start));
			$end = date($time_fmt, strtotime($time_end));
			echo sprintf('%s - %s', $start, $end);
		}
		?>
	</span>
</td>
