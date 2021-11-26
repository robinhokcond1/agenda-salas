<?php
echo $this->session->flashdata('saved');
echo form_open('settings', array('id'=>'settings', 'class'=>'cssform'));
?>


<fieldset>

	<legend accesskey="S" tabindex="<?php echo tab_index() ?>">configurações</legend>

	<p>
		<label for="bia">Reserva com antecedência</label>
		<?php
		$value = (int) set_value('bia', element('bia', $settings), FALSE);
		echo form_input(array(
			'name' => 'bia',
			'id' => 'bia',
			'size' => '5',
			'maxlength' => '3',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Quantos dias no futuro os usuários podem fazer suas próprias reservas. Enter <span>0</span> para nenhuma restrição.</p>
	</p>
	<?php echo form_error('bia') ?>

	<p>
		<label for="num_max_bookings">Número máximo de reservas ativas</label>
		<?php
		$value = (int) set_value('num_max_bookings', element('num_max_bookings', $settings), FALSE);
		echo form_input(array(
			'name' => 'num_max_bookings',
			'id' => 'num_max_bookings',
			'size' => '5',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Número máximo de reservas únicas ativas para um usuário. Entrar <span>0</span> sem limite.</p>
		<p class="hint">'Ativo' é qualquer reserva única para uma data e um período de início no período futuro.</p>
	</p>
	<?php echo form_error('num_max_bookings') ?>

	<hr size="1" />

	<p>
		<label for="displaytype">Exibição tipos de reservas</label>
		<?php
		$displaytype = set_value('displaytype', element('displaytype', $settings), FALSE);
		$options = array(
			'day' => 'Um dia de cada vez',
			'room' => 'Uma sala de cada vez',
		);
		echo form_dropdown(
			'displaytype',
			$options,
			$displaytype,
			' id="displaytype" tabindex="' . tab_index() . '"'
		);
		?>
		<p class="hint">Especifique o foco principal da página de reservas.<br />
			<strong><span>Um dia de cada vez</span></strong> - todos os períodos e salas são mostrados para a data selecionada.<br />
			<strong><span>Um sala de cada vez</span></strong> - todos os períodos e dias da semana são mostrados para a sala selecionada.
		</p>
	</p>
	<?php echo form_error('displaytype'); ?>

	<p>
		<label for="columns">Colunas de reservas</label>
		<?php
		$columns = set_value('d_columns', element('d_columns', $settings), FALSE);
		?>
		<select name="d_columns" id="d_columns" tabindex="<?php echo tab_index() ?>">
			<option value="periods" class="day room" <?= $columns == 'periods' ? 'selected="selected"' : '' ?>>Períodos</option>
			<option value="rooms" class="day" <?= $columns == 'rooms' ? 'selected="selected"' : '' ?>>Salas</option>
			<option value="days" class="room" <?= $columns == 'days' ? 'selected="selected"' : '' ?>>Dias</option>
		</select>
		<p class="hint">Selecione quais detalhes você deseja exibir na parte superior da página de reservas.</p>
	</p>
	<?php echo form_error('d_columns') ?>

</fieldset>




<fieldset>

	<legend accesskey="D" tabindex="<?php echo tab_index() ?>">Formatos de data</legend>

	<div>
		As datas seguem o formato PHP - <a href="https://www.php.net/manual/en/function.date.php#refsect1-function.date-parameters" target="_blank">view reference</a>.
	</div>

	<p>
		<label for="date_format_long">Formato de data longa</label>
		<?php
		$value = set_value('date_format_long', element('date_format_long', $settings), FALSE);
		echo form_input(array(
			'name' => 'date_format_long',
			'id' => 'date_format_long',
			'size' => '15',
			'maxlength' => '10',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Formato de data longo exibido na parte superior da página de reservas.</p>
	</p>
	<?php echo form_error('date_format_long') ?>

	<p>
		<label for="date_format_weekday">Formato da data do dia da semana</label>
		<?php
		$value = set_value('date_format_weekday', element('date_format_weekday', $settings), FALSE);
		echo form_input(array(
			'name' => 'date_format_weekday',
			'id' => 'date_format_weekday',
			'size' => '15',
			'maxlength' => '10',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Formato de data curto para um dia da semana específico.</p>
	</p>
	<?php echo form_error('date_format_weekday') ?>

	<p>
		<label for="time_format_period">Formato da hora do período</label>
		<?php
		$value = set_value('time_format_period', element('time_format_period', $settings), FALSE);
		echo form_input(array(
			'name' => 'time_format_period',
			'id' => 'time_format_period',
			'size' => '15',
			'maxlength' => '10',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Formato de hora para períodos.</p>
	</p>
	<?php echo form_error('time_format_period') ?>


</fieldset>


<fieldset>

	<legend accesskey="M" tabindex="<?php echo tab_index() ?>">Modo de manutenção</legend>

	<div>A ativação do Modo de manutenção impede que as contas de usuário visualizem e façam reservas. Todos os usuários ainda podem fazer login para fazer alterações em sua própria conta ou alterar sua senha.</div>

	<p>
		<label for="maintenance_mode">Modo de manutenção</label>
		<?php
		$value = set_value('maintenance_mode', element('maintenance_mode', $settings, '0'), FALSE);
		echo form_hidden('maintenance_mode', '0');
		echo form_checkbox(array(
			'name' => 'maintenance_mode',
			'id' => 'maintenance_mode',
			'value' => '1',
			'tabindex' => tab_index(),
			'checked' => ($value == '1')
		));
		?>
	</p>


	<p>
		<label for="maintenance_mode_message">Menssage</label>
		<?php
		$field = 'maintenance_mode_message';
		$value = set_value($field, element($field, $settings, ''), FALSE);
		echo form_textarea(array(
			'name' => $field,
			'id' => $field,
			'rows' => '5',
			'cols' => '60',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
		<p class="hint">Esta é a mensagem que será exibida durante o modo de manutenção.</p>
	</p>
	<?php echo form_error($field) ?>

</fieldset>


<script type="text/javascript">
Q.push(function() {
	dynamicSelect('displaytype', 'd_columns');
});
</script>


<?php

$this->load->view('partials/submit', array(
	'submit' => array('Save', tab_index()),
	'cancel' => array('Cancel', tab_index(), 'controlpanel'),
));

echo form_close();
