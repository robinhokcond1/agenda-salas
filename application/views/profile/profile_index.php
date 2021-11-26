<?php
echo $this->session->flashdata('saved');

echo iconbar(array(
	array('profile/edit', 'Editar meus detalhes', 'user_edit.png'),
));

?>

<?php if($myroom){ ?>
<h3>Reservas de funcionários em minhas salas</h3>
<ul>
<?php
foreach($myroom as $booking){
	$string = '<li>%s está reservado em %s by %s para %s. %s</li>';
	if($booking->notes){ $booking->notes = '('.$booking->notes.')'; }
	if(!$booking->displayname){ $booking->displayname = $booking->username; }
	echo sprintf($string, html_escape($booking->name), date("d/m/Y", strtotime($booking->date)), html_escape($booking->displayname), html_escape($booking->periodname), html_escape($booking->notes));
}
?>
</ul>
<?php } ?>



<?php if($mybookings){ ?>
<h3>Minhas reservas</h3>
<ul>
<?php
foreach($mybookings as $booking){
	$string = '<li>%s está reservado em %s para %s. %s.</li>';
	$notes = '';
	if($booking->notes){ $notes = '('. $booking->notes.')'; }
	echo sprintf($string, html_escape($booking->name), date("d/m/Y", strtotime($booking->date)), html_escape($booking->periodname), html_escape($notes));
}
?>
</ul>
<?php } ?>


<h3>Minhas reservas totais</h3>
<ul>
	<li>Número de reservas já feitas: <?php echo $total['all'] ?></li>
	<li>Número de reservas deste ano até o momento: <?php echo $total['yeartodate'] ?></li>
	<li>Número de reservas ativas atuais: <?php echo $total['active'] ?></li>
</ul>
