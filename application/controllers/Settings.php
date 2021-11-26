<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{


	public function __construct()
	{
		parent::__construct();
		$this->require_auth_level(ADMINISTRATOR);
	}


	/**
	* Settings page
	*
	*/
	function index()
	{
		$this->data['settings'] = $this->settings_model->get_all('crbs');

		$this->data['title'] = 'Configurações';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('settings/settings', $this->data, TRUE);

		if ($this->input->post()) {
			$this->save_settings();
		}

		return $this->render();
	}



	/**
	* Controller function to handle submitted form
	*
	*/
	private function save_settings()
	{
		// Parse data input from view and carry out appropriate action.

		// Load image manipulation library
		$this->load->library('image_lib');

		$this->load->library('form_validation');
		$this->form_validation->set_rules('bia', 'Booking in advance', 'max_length[3]|numeric');
		$this->form_validation->set_rules('num_max_bookings', 'Maximum number of bookings', 'max_length[3]|numeric');
		$this->form_validation->set_rules('displaytype', 'Display type', 'required');
		$this->form_validation->set_rules('d_columns', 'Display columns', 'callback__valid_columns');
		$this->form_validation->set_rules('date_format_long', 'Long date format', 'required|max_length[15]');
		$this->form_validation->set_rules('date_format_weekday', 'Weekday date format', 'max_length[15]');
		$this->form_validation->set_rules('time_format_period', 'Period time format', 'max_length[15]');
		$this->form_validation->set_rules('maintenance_mode', 'Maintenance mode', 'is_natural');
		$this->form_validation->set_rules('maintenance_mode_message', 'Maintenance mode message', 'max_length[1024]');

		if ($this->form_validation->run() == FALSE) {
			echo validation_errors();
			return FALSE;
		}

		$settings = array(
			'bia' => (int) $this->input->post('bia'),
			'num_max_bookings' => (int) $this->input->post('num_max_bookings'),
			'displaytype' => $this->input->post('displaytype'),
			'd_columns' => $this->input->post('d_columns'),
			'date_format_long' => $this->input->post('date_format_long'),
			'date_format_weekday' => $this->input->post('date_format_weekday'),
			'time_format_period' => $this->input->post('time_format_period'),
			'maintenance_mode' => $this->input->post('maintenance_mode'),
			'maintenance_mode_message' => $this->input->post('maintenance_mode_message'),
		);

		$settings['colour'] = '468ED8';

		// Set default date value if empty
		$date_format_long = trim($settings['date_format_long']);
		if ( ! strlen($date_format_long)) {
			$settings['date_format_long'] = 'l jS F Y';
		}

		$this->settings_model->set($settings);

		$this->session->set_flashdata('saved', msgbox('info', 'As configurações foram atualizadas.'));

		redirect('settings');
	}


	function _valid_columns($cols)
	{
		// Day: Periods / Rooms
		// Room: Periods / Days
		$valid['day'] = array('periods', 'rooms');
		$valid['room'] = array('periods', 'days');

		$displaytype = $this->input->post('displaytype');

		switch ($displaytype) {

			case 'day':
				if (in_array($cols, $valid['day'])) {
					$ret = TRUE;
				} else {
					$ret = FALSE;
				}
			break;

			case 'room':
				if (in_array($cols, $valid['room'])) {
					$ret = TRUE;
				} else {
					$ret = FALSE;
				}
			break;
		}

		if ($ret == FALSE) {
			$this->form_validation->set_message('_valid_columns', 'A coluna que você selecionou é incompatível com o tipo de exibição.');
		}

		return $ret;
	}


}
