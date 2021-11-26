<?php

if (isset($roomphoto)){
	$width = 760;
} else {
	$width = 400;
}

$url = site_url('rooms/info/'.$room_id);
$name = html_escape($name);
$title = '<a onclick="window.open(\''.$url.'\',\'\',\'width='.$width.',height=360,scrollbars\');return false;" href="'.$url.'" title="View More Information">'.$name.'</a>'."\n";
?>

<td align="center" width="<?php echo $width ?>">
	<strong><?php echo $title ?></strong><br />
	<span style="font-size:90%">
		<?php echo html_escape(($displayname == '') ? $username : $displayname); ?> &nbsp;
	</span>
</td>
