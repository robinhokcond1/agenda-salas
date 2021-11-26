<?php

if ($this->userauth->loggedin()) {
	$menu[1]['text'] = img('assets/images/ui/link_controlpanel.png', FALSE, 'hspace="4" align="top" alt=" "') . 'Painel de Controle';
	$menu[1]['href'] = site_url('controlpanel');
	$menu[1]['title'] = 'Ferramentas';

	if($this->userauth->is_level(ADMINISTRATOR)){ $icon = 'user_administrator.png'; } else { $icon = 'user_teacher.png'; }
	$menu[3]['text'] = img('assets/images/ui/logout.png', FALSE, 'hspace="4" align="top" alt=" "') . 'Sair';
	$menu[3]['href'] = site_url('logout');
	$menu[3]['title'] = 'Sair do Sistema de Reservas de Salas';
}
?>

<!DOCTYPE html>
<html>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="author" content="Craig A Rodway">
	<title><?= html_escape($title) ?> | GrupoAdrianoCobuccio</title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?= base_url('assets/style.css') ?>">
	<link rel="stylesheet" type="text/css" media="print" href="<?= base_url('assets/print.css') ?>">
	<link rel="stylesheet" type="text/css" media="screen" href="<?= base_url('assets/sorttable.css') ?>">
	<link rel="stylesheet" type="text/css" media="screen" href="<?= base_url('assets/datepicker.css') ?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/brand/apple-touch-icon.png') ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/brand/favicon-32x32.png') ?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/brand/favicon-16x16.png') ?>">
	<link rel="manifest" href="<?= base_url('assets/brand/site.webmanifest') ?>">
	<link rel="mask-icon" href="<?= base_url('assets/brand/safari-pinned-tab.svg') ?>" color="#ff6400">
	<link rel="shortcut icon" href="<?= base_url('assets/brand/favicon.ico') ?>">
	<meta name="msapplication-TileColor" content="#ff6400">
	<meta name="msapplication-config" content="<?= base_url('assets/brand/browserconfig.xml') ?>">
	<meta name="theme-color" content="#ff6400">
	<script>
	var h = document.getElementsByTagName("html")[0];
	(h ? h.classList.add('js') : h.className += ' ' + 'js');
	var Q = [];
	var BASE_URL = "<?= base_url() ?>";
	</script>
</head>
<body style="background-image:url('<?= base_url('assets/images/bg/global.png') ?>')">

	<?php
	if (setting('maintenance_mode') == 1) {
		$message = setting('maintenance_mode_message');
		if ( ! strlen($message)) {
			$message = 'Atualmente, as reservas da sala de aula estão no modo de manutenção. Verifique novamente em breve.';
		}
		echo "<div class='maintenance-wrapper'>";
		echo "<div class='outer'>";
		echo html_escape($message);
		echo "</div>";
		echo "</div>";
	}
	?>
	<div class="outer">

		<div class="header">

			<div class="nav-box">
				<?php if( ! $this->userauth->loggedin()) { echo '<br /><br />'; } ?>
				<?php
				$i=0;
				if(isset($menu)){
					foreach( $menu as $link ){
						echo "\n".'<a href="'.$link['href'].'" title="'.$link['title'].'">'.$link['text'].'</a>'."\n";
						if( $i < count($menu)-1 ){ echo img('assets/images/blank.png', FALSE, 'width="16" height="16"'); }
						$i++;
					}
				}
				?><br />
				<?php
				if ($this->userauth->loggedin()) {
					$output = html_escape(strlen($this->userauth->user->displayname) > 1 ? $this->userauth->user->displayname : $this->userauth->user->username);
					echo "<p class='normal'>logado como {$output}</p>";
				}
				?>
			</div>

			<br />

			<span class="title">
				<?php
				$name = '';
				if (config_item('is_installed')) {
					$name = setting('name');
				}
				if (strlen($name)) {
					echo anchor('/', html_escape($name));
				} else {
					$attrs = "title='Reservas de Salas' style='font-weight:normal;color:#0081C2;letter-spacing:-2px'";
					$output = "classroom";
					$output .= "<span style='color:#ff6400;font-weight:bold'>bookings</span>";
					echo anchor('/', $output, $attrs);
				}
				?>
			</span>

		</div>

		<?php if (isset($midsection)): ?>
			<div class="mid-section" align="center">
				<h1 style="font-weight:normal"><?php echo $midsection ?></h1>
			</div>
		<?php endif; ?>

		<div class="content_area">
			<?php if(isset($showtitle)){ echo '<h2>'.html_escape($showtitle).'</h2>'; } ?>
			<?php echo $body ?>
		</div>

		<div class="footer">
			<br />

			<div id="footer">
				<?php
				if (isset($menu)) {
					foreach( $menu as $link ) {
						echo "\n".'<a href="'.$link['href'].'" title="'.$link['title'].'">'.$link['text'].'</a>'."\n";
						echo img('assets/images/blank.png', FALSE, 'width="16" height="10" alt=" "');
					}
				}
				?>
				<br /><br />
				<span style="font-size:90%;color:#678; line-height: 2">
					<a href="https://www.brasilcard.net" target="_blank">Brasilcard</a> version <?= VERSION ?>.
					&copy; <?= date('Y') ?> Desenvolvido por: Robson Ramos.
					<br />
					Load time: <?php echo $this->benchmark->elapsed_time() ?> seconds.
				</span>
				<br /><br />
			</div>
		</div>
	</div>

	<div id="tipDiv" style="position:absolute; visibility:hidden; z-index:100"></div>

	<?php
	$scripts = array();
	$scripts[] = base_url('assets/js/prototype.lite.js');
	$scripts[] = base_url('assets/js/util.js');
	$scripts[] = base_url('assets/js/sorttable.js');
	$scripts[] = base_url('assets/js/datepicker.js');
	// $scripts[] = base_url('assets/js/imagepreview.js');

	foreach ($scripts as $script)
	{
		echo "<script type='text/javascript' src='{$script}'></script>\n";
	}

	?>

	<script>
	(function() {
		if (typeof(window['Q']) !== "undefined") {
			for (var i = 0, len = Q.length; i < len; i++) {
				Q[i]();
			}
		}
	})();
	</script>

</body>
</html>
