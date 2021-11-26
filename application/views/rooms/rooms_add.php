<?php
$room_id = NULL;
if (isset($room) && is_object($room)) {
	$room_id = set_value('room_id', $room->room_id);
}

echo form_open_multipart('rooms/save', array('class' => 'cssform', 'id' => 'rooms_add'), array('room_id' => $room_id) );

?>

<fieldset>

	<legend accesskey="R" tabindex="<?php echo tab_index() ?>">Detalhes da sala</legend>

	<p>
		<label for="name" class="required">Nome</label>
		<?php
		$field = 'name';
		$value = set_value($field, isset($room) ? $room->name : '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '20',
			'maxlength' => '20',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="location">Localização</label>
		<?php
		$field = 'location';
		$value = set_value($field, isset($room) ? $room->location : '', FALSE);
		echo form_input(array(
			'name' => $field,
			'id' => $field,
			'size' => '30',
			'maxlength' => '40',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="user_id">Colaborador</label>
		<?php
		$userlist = array('' => '(Nenhum)');
		foreach ($users as $user) {
			$label = empty($user->displayname) ? $user->username : $user->displayname;
			$userlist[ $user->user_id ] = html_escape($label);
		}
		$field = 'user_id';
		$value = set_value($field, isset($room) ? $room->user_id : '', FALSE);
		echo form_dropdown($field, $userlist, $value, 'tabindex="'.tab_index().'"');
		?>
	</p>
	<?php echo form_error($field); ?>

	<p>
		<label for="notes">Referência</label>
		<?php
		$field = 'notes';
		$value = set_value($field, isset($room) ? $room->notes : '', FALSE);
		echo form_textarea(array(
			'name' => $field,
			'id' => $field,
			'rows' => '5',
			'cols' => '30',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error($field) ?>

	<p>
		<label for="bookable">Pode ser reservado</label>
		<?php
		$field = 'bookable';
		$value = isset($room) ? $room->bookable : '1';
		$checked = set_checkbox($field, '1', $value == '1');
		echo form_hidden($field, '0');
		echo form_checkbox(array(
			'name' => $field,
			'id' => $field,
			'value' => '1',
			'tabindex' => tab_index(),
			'checked' => $checked,
		));
		?>
		<p class="hint">Marque esta caixa para permitir que reservas sejam feitas nesta sala</p>
	</p>

</fieldset>


<fieldset>

	<legend accesskey="P" tabindex="7">Foto</legend>

	<br>
	<div>Adicione uma foto da sala que os usuários poderão visualizar.</div>
	<br>

	<p>
		<label>Foto atual</label>
		<?php
		if (isset($room) && isset($room->photo) && ! empty($room->photo)) {
			$path = "uploads/{$room->photo}";
			if (file_exists(FCPATH . $path)) {
				$url = base_url($path);
				$img = img($path, FALSE, "width='200' style='width:200px;height:auto;max-width:200px;padding:1px;border:1px solid #ccc'");
				echo anchor($url, $img);
			} else {
				echo '<em>Nenhum</em>';
			}
		} else {
			echo '<em>Nenhum</em>';
		}
		?>
	</p>

	<p>
		<label for="userfile">Upload de arquivo</label>
		<?php
		echo form_upload(array(
			'name' => 'userfile',
			'id' => 'userfile',
			'size' => '30',
			'maxlength' => '255',
			'tabindex' =>tab_index(),
			'value' => '',
		));
		?>
		<br>
		<br>
		<p class="hint">Tamanho máximo do arquivo <span><?php echo $max_size_human ?></span>.</p>
		<p class="hint">Carregar uma nova foto irá <span>substituir</span> o atual.</p>
	</p>

	<?php
	if ($this->session->flashdata('image_error') != '' ) {
		$err = $this->session->flashdata('image_error');
		echo "<p class='hint error'><span>{$err}</span></p>";
	}
	?>

	<?php if (isset($room) && ! empty($room->photo)): ?>

	<p>
		<label for="photo_delete">Excluir foto?</label>
		<?php
		$field = 'photo_delete';
		echo form_hidden($field, '0');
		echo form_checkbox(array(
			'name' => $field,
			'id' => $field,
			'value' => '1',
			'tabindex' => tab_index(),
			'checked' => FALSE,
		));
		?>
		<p class="hint">Marque esta caixa para <span>remover a foto atual</span> sem adicionar um novo.</p>
	</p>

	<?php endif; ?>

</fieldset>


<?php if (isset($fields) && is_array($fields)): ?>

<fieldset>

	<legend accesskey="F" tabindex="<?php echo tab_index() ?>">Campos</legend>

	<?php

	foreach ($fields as $field) {

		echo '<p>';
		echo '<label>' . $field->name . '</label>';

		switch ($field->type) {

			case Rooms_model::FIELD_TEXT:

				$input = "f{$field->field_id}";
				$value = set_value($input, element($field->field_id, $fieldvalues), FALSE);
				echo form_input(array(
					'name' => $input,
					'id' => $input,
					'size' => '30',
					'maxlength' => '255',
					'tabindex' => tab_index(),
					'value' => $value,
				));

			break;


			case Rooms_model::FIELD_SELECT:

				$input = "f{$field->field_id}";
				$value = set_value($input, element($field->field_id, $fieldvalues), FALSE);
				$options = $field->options;
				$opts = array();
				foreach ($options as $option) {
					$opts[$option->option_id] = html_escape($option->value);
				}
				echo form_dropdown($input, $opts, $value, 'tabindex="'.tab_index().'"');

			break;


			case Rooms_model::FIELD_CHECKBOX:

				$input = "f{$field->field_id}";
				$checked = set_checkbox($input, '1', element($field->field_id, $fieldvalues) == '1');
				echo form_hidden($input, '0');
				echo form_checkbox(array(
					'name' => $input,
					'id' => $input,
					'value' => '1',
					'tabindex' => tab_index(),
					'checked' => $checked,
				));

			break;

		}
		echo '</p>';

	}

	?>

</fieldset>

<?php endif; ?>


<?php

$this->load->view('partials/submit', array(
	'submit' => array('Salvar', tab_index()),
	'cancel' => array('Cancelar', tab_index(), 'rooms'),
));

echo form_close();
