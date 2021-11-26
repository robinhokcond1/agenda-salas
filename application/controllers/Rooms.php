<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rooms extends MY_Controller
{




	public function __construct()
	{
		parent::__construct();

		$this->require_logged_in();

		$this->load->model('crud_model');
		$this->load->model('rooms_model');
		$this->load->model('users_model');
		$this->load->helper('number');

		$this->data['max_size_bytes'] = max_upload_file_size();
		$this->data['max_size_human'] = byte_format(max_upload_file_size());
	}



	function info($room_id = NULL)
	{
		if (empty($room_id))
		{
			show_error('Não há sala para mostrar.');
		}

		$this->data['room'] = $this->rooms_model->Get($room_id);

		if (empty($this->data['room'])) {
			show_error("Não foi possível encontrar o reserva solicitada.", 404, "Reserva não encontrada.");
		}

		$this->data['fields'] = $this->rooms_model->GetFields();
		$this->data['fieldvalues'] = $this->rooms_model->GetFieldValues($room_id);

		$this->data['body'] = $this->load->view('rooms/room_info', $this->data, TRUE);
		$this->data['title'] = $this->data['room']->name;

		return $this->render('minilayout');
	}





	function index()
	{
		$this->require_auth_level(ADMINISTRATOR);

		$this->data['rooms'] = $this->rooms_model->Get();

		$this->data['title'] = 'Salas';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('rooms/rooms_index', $this->data, TRUE);

		return $this->render();
	}





	/**
	 * Controller function to handle the Add page
	 */
	function add()
	{
		$this->require_auth_level(ADMINISTRATOR);

		// Get list of users
		$this->data['users'] = $this->users_model->Get(NULL, NULL, NULL);
		$this->data['fields'] = $this->rooms_model->GetFields();
		$this->data['fieldvalues'] = array();

		$this->data['title'] = 'Adicionar Sala';
		$this->data['showtitle'] = $this->data['title'];

		$columns = array(
			'c1' => array(
				'content' => $this->load->view('rooms/rooms_add', $this->data, TRUE),
				'width' => '70%',
			),
			'c2' => array(
				'content' => $this->load->view('rooms/rooms_add_side', $this->data, TRUE),
				'width' => '30%',
			),
		);

		$this->data['body'] = $this->load->view('columns', $columns, TRUE);

		return $this->render();
	}





	/**
	 * Controller function to handle an edit
	 */
	function edit($id = NULL)
	{
		$this->require_auth_level(ADMINISTRATOR);

		$this->data['room'] = $this->rooms_model->Get($id);

		if (empty($this->data['room'])) {
			show_404();
		}

		$this->data['users'] = $this->users_model->Get(NULL, NULL, NULL);
		$this->data['fields'] = $this->rooms_model->GetFields();
		$this->data['fieldvalues'] = $this->rooms_model->GetFieldValues($id);

		$this->data['title'] = 'Editar Sala';
		$this->data['showtitle'] = $this->data['title'];

		$columns = array(
			'c1' => array(
				'content' => $this->load->view('rooms/rooms_add', $this->data, TRUE),
				'width' => '70%',
			),
			'c2' => array(
				'content' => $this->load->view('rooms/rooms_add_side', $this->data, TRUE),
				'width' => '30%',
			),
		);

		$this->data['body'] = $this->load->view('columns', $columns, TRUE);

		return $this->render();
	}




	/**
	 * Save
	 *
	 */
	function save()
	{
		$this->require_auth_level(ADMINISTRATOR);

		$room_id = $this->input->post('room_id');

		$this->load->library('form_validation');

		$this->form_validation->set_rules('room_id', 'ID', 'integer');
		$this->form_validation->set_rules('name', 'Name', 'required|min_length[1]|max_length[20]');
		$this->form_validation->set_rules('user_id', 'User', 'integer');
		$this->form_validation->set_rules('location', 'Location', 'max_length[40]');
		$this->form_validation->set_rules('notes', 'Notes', 'max_length[255]');
		$this->form_validation->set_rules('bookable', 'Bookable', 'integer');

		if ($this->form_validation->run() == FALSE) {
			return (empty($room_id) ? $this->add() : $this->edit($room_id));
		}

		$room_data = array(
			'name' => $this->input->post('name'),
			'user_id' => $this->input->post('user_id'),
			'location' => $this->input->post('location'),
			'notes' => $this->input->post('notes'),
			'bookable' => $this->input->post('bookable'),
		);

		if (empty($room_id)) {

			$room_id = $this->rooms_model->add($room_data);

			if ($room_id) {
				$line = sprintf($this->lang->line('crbs_action_added'), $room_data['name']);
				$flashmsg = msgbox('info', $line);
			} else {
				$line = sprintf($this->lang->line('crbs_action_dberror'), 'adding');
				$flashmsg = msgbox('error', $line);
			}

		} else {

			if ($this->rooms_model->edit($room_id, $room_data)) {
				$line = sprintf($this->lang->line('crbs_action_saved'), $room_data['name']);
				$flashmsg = msgbox('info', $line);
			} else {
				$line = sprintf($this->lang->line('crbs_action_dberror'), 'editing');
				$flashmsg = msgbox('error', $line);
			}

		}

		$this->session->set_flashdata('saved', $flashmsg);

		// Process image things
		//
		$image_status = $this->process_image($room_id);
		if ( ! $image_status) {
			return (empty($room_id) ? $this->add() : $this->edit($room_id));
		}

		// Process field-related things
		//
		$fields_status = $this->process_fields($room_id);
		if ( ! $fields_status) {
			return (empty($room_id) ? $this->add() : $this->edit($room_id));
		}

		redirect('rooms');
	}


	/**
	 * Handle the uploading of an image when saving a room.
	 *
	 */
	private function process_image($room_id = NULL)
	{
		if (empty($room_id)) {
			return TRUE;
		}

		if ($this->input->post('photo_delete')) {
			$this->rooms_model->delete_photo($room_id);
		}

		$has_image = (isset($_FILES['userfile'])
		              && isset($_FILES['userfile']['name'])
		              && ! empty($_FILES['userfile']['name']));

		if ( ! $has_image) {
			return TRUE;
		}

		// Upload config
		//

		$upload_config = array(
			'upload_path' => FCPATH . 'uploads',
			'allowed_types' => 'jpg|jpeg|png|gif',
			'max_size' => $this->data['max_size_bytes'],
			'encrypt_name' => TRUE,
		);

		$this->load->library('upload', $upload_config);

		if ( ! $this->upload->do_upload()) {
			$error = $this->upload->display_errors('','');
			$this->session->set_flashdata('image_error', $error);
			$image_error = $error;
			return FALSE;
		}

		// File uploaded
		//

		$upload_data = $this->upload->data();

		$this->load->library('image_lib');

		$image_config = array(
			'image_library' => 'gd2',
			'source_image' => $upload_data['full_path'],
			'maintain_ratio' => TRUE,
			'width' => 1280,
			'height' => 1280,
			'master_dim' => 'auto',
		);

		$this->image_lib->initialize($image_config);

		$res = $this->image_lib->resize();

		if ( ! $res) {
			$this->session->set_flashdata('image_error', $this->image_lib->display_errors());
			return FALSE;
		}

		// Remove previous photo
		$this->rooms_model->delete_photo($room_id);

		// Update DB with new photo
		$this->rooms_model->edit($room_id, array(
			'photo' => $upload_data['file_name'],
		));

		return TRUE;
	}


	/**
	 * Process the updating of field values when saving a room
	 *
	 */
	private function process_fields($room_id = NULL)
	{
		if (empty($room_id)) {
			return TRUE;
		}

		$fieldvalues = array();
		$fields = $this->rooms_model->GetFields();
		$fields = (is_array($fields) ? $fields : array());

		foreach ($fields as $field) {
			$key = $field->field_id;
			$value = $this->input->post("f{$key}");
			$fieldvalues[ $key ] = $value;
		}

		return $this->rooms_model->save_field_values($room_id, $fieldvalues);
	}




	/**
	 * Controller function to delete a room
	 *
	 */
	function delete($id = NULL)
	{
		$this->require_auth_level(ADMINISTRATOR);

		if ($this->input->post('id')) {
			$this->rooms_model->delete($this->input->post('id'));
			$flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
			$this->session->set_flashdata('saved', $flashmsg);
			redirect('rooms');
		}

		$this->data['action'] = 'rooms/delete';
		$this->data['id'] = $id;
		$this->data['cancel'] = 'rooms';
		$this->data['text'] = 'Se você excluir esta sala, <strong>todas as reservas</strong> para esta sala será <strong>excluído permanentemente</strong> também.';

		$row = $this->rooms_model->Get($id);
		$this->data['title'] = 'Excluir sala ('.html_escape($row->name).')';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);

		return $this->render();
	}




	/**
	 * Fields
	 *
	 */





	function fields()
	{
		$this->require_auth_level(ADMINISTRATOR);

		$this->data['options_list'] = $this->rooms_model->options;
		$this->data['fields'] = $this->rooms_model->GetFields();
		$this->data['title'] = 'Campos da Sala';
		$this->data['showtitle'] = 'Os campos personalizados';
		$this->data['body'] = $this->load->view('rooms/fields/index', $this->data, TRUE);

		return $this->render();
	}





	function add_field()
	{
		$this->require_auth_level(ADMINISTRATOR);

		$this->data['options_list'] = $this->rooms_model->options;

		$this->data['title'] = 'Adicionar campo';
		$this->data['showtitle'] = $this->data['title'];

		$columns = array(
			'c1' => array(
				'content'=> $this->load->view('rooms/fields/add', $this->data, TRUE),
				'width' => '70%',
			),
			'c2' => array(
				'content' => '',
				'width' => '30%',
			),
		);

		$this->data['body'] = $this->load->view('columns', $columns, TRUE);

		return $this->render();
	}




	/**
	 * Controller function to handle an edit
	 *
	 */
	function edit_field($id = NULL)
	{
		$this->require_auth_level(ADMINISTRATOR);

		$this->data['field'] = $this->rooms_model->GetFields($id);
		$this->data['options_list'] = $this->rooms_model->options;

		$this->data['title'] = 'Editar campo';
		$this->data['showtitle'] = $this->data['title'];

		$columns = array(
			'c1' => array(
				'content'=> $this->load->view('rooms/fields/add', $this->data, TRUE),
				'width' => '70%',
			),
			'c2' => array(
				'content' => '',
				'width' => '30%',
			),
		);

		$this->data['body'] = $this->load->view('columns', $columns, TRUE);

		return $this->render();
	}




	function save_field()
	{
		$this->require_auth_level(ADMINISTRATOR);

		// Get ID from form
		$field_id = $this->input->post('field_id');

		$this->load->library('form_validation');

		$this->form_validation->set_rules('field_id', 'ID', 'integer');
		$this->form_validation->set_rules('name', 'Name', 'required|min_length[1]|max_length[64]');
		$this->form_validation->set_rules('options', 'Items', '');

		if ($this->form_validation->run() == FALSE){
			return (empty($field_id) ? $this->add_field() : $this->edit_field($field_id));
		}

		// Validation succeeded!
		$field_data = array(
			'name' => $this->input->post('name'),
			'type' => $this->input->post('type'),
			'options' => $this->input->post('options'),
		);

		if (empty($field_id)) {
			$field_id = $this->rooms_model->field_add($field_data);
			$flashmsg = msgbox('info', "O {$field_data['name']} campo foi adicionado.");
		} else {
			$this->rooms_model->field_edit($field_id, $field_data);
			$flashmsg = msgbox('info', "O {$field_data['name']} campo foi atualizado.");
		}

		$this->session->set_flashdata('saved', $flashmsg, TRUE);
		redirect('rooms/fields');
	}





	/**
	 * Delete a field
	 *
	 */
	function delete_field($id = NULL)
	{
		$this->require_auth_level(ADMINISTRATOR);

		if ($this->input->post('id')) {
			$this->rooms_model->field_delete($this->input->post('id'));
			$flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
			return redirect('rooms/fields');
		}

		$this->data['action'] = 'rooms/delete_field';
		$this->data['id'] = $id;
		$this->data['cancel'] = 'rooms/fields';

		$row = $this->rooms_model->GetFields($id);
		$this->data['title'] = 'Excluir campo ('.html_escape($row->name).')';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);

		return $this->render();
	}





}
