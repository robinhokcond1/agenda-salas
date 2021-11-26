<?php

class Bookings_model extends CI_Model
{


	var $table_headings = '';
	var $table_rows = array();


	public function __construct()
	{
		parent::__construct();
	}




	function Get($booking_id)
	{
		$this->db->from('bookings');
		$this->db->where('booking_id', $booking_id);

		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			return FALSE;
		}
	}




	function GetByDate($date = NULL)
	{
		if ($date == NULL) {
			$date = date("Y-m-d");
		}

		$day_num = date('N', strtotime($date));
		$query_str = "SELECT * FROM bookings WHERE (`date`='$date' OR day_num=$day_num)";
		$query = $this->db->query($query_str);
		$result = $query->result_array();
		return $result;
	}


	function GetUnique($params = array())
	{
		$defaults = array(
			'booking_id' => NULL,
			'date' => NULL,
			'period_id' => 0,
			'room_id' => 0,
			'week_id' => 0,
			'day_num' => NULL,
		);

		$data = array_merge($defaults, $params);

		if (empty($data['week_id'])) {
			$week = $this->WeekObj(strtotime($data['date']));
			$week_id = ($week ? $week->week_id : 0);
		} else {
			$week_id = $data['week_id'];
		}

		if ( ! strlen($data['day_num'])) {
			$day_num = date('N', strtotime($data['date']));
		} else {
			$day_num = $data['day_num'];
		}

		$sql = "SELECT *
				FROM bookings
				WHERE period_id = ?
				AND room_id = ?";

		if ( ! empty($data['date'])) {
			$date_escaped = $this->db->escape($data['date']);
			$sql .= " AND (`date` = {$date_escaped} OR (day_num = {$day_num} AND week_id = {$week_id}))";
		} else {
			$sql .= " AND (day_num = {$day_num} AND week_id = {$week_id}) ";
		}

		if ( ! empty($data['booking_id'])) {
			$sql .= " AND booking_id != " . $this->db->escape($data['booking_id']);
		}

		$query = $this->db->query($sql, array(
			$data['period_id'],
			$data['room_id'],
		));

		return $query->result_array();
	}




	function TableAddColumn($td)
	{
		$this->table_headings .= $td;
	}




	function TableAddRow($data)
	{
		$this->table_rows[] = $data;
	}




	function Table()
	{
		$table = '<tr>' . $this->table_headings . '</tr>';
		/* foreach($this->table_rows as $row){
			$table .= '<tr>' . $row . '</tr>';
		} */
		return $table;
	}




	function BookingCell($data, $key, $rooms, $users, $room_id, $url, $booking_date_ymd = '', $holidays = array())
	{

		// Check if there is a booking
		if(isset($data[$key])){

			// There's a booking for this ID, set var
			$booking = $data[$key];

			if($booking->date == NULL){
			// If no date set, then it's a static/timetable/recurring booking
				$cell['class'] = 'static';
				$cell['body']= '';
			} else {
			// Date is set, it's a once off staff booking
				$cell['class'] = 'staff';
				$cell['body'] = '';
			}

		// Username info
			if(isset($users[$booking->user_id])){
				$username = $users[$booking->user_id]->username;
				$displayname = trim($users[$booking->user_id]->displayname);
				if(strlen($displayname) < 2){ $displayname = $username; }
				$cell['body'] .= '<strong>'.html_escape($displayname).'</strong>';
				$user = 1;
			}

			// Any notes?
			if($booking->notes){
				if(isset($user)){ $cell['body'] .= '<br />'; }
				$notes = html_escape($booking->notes);
				$cell['body'] .= '<span title="'.$notes.'">'.character_limiter($notes, 15).'</span>';
			}

			// Edit if admin?
			 if($this->userauth->is_level(ADMINISTRATOR)){
				$edit_url = site_url('bookings/edit/'.$booking->booking_id);
				$src = base_url('assets/images/ui/edit.png');
				$cell['body'] .= '<br /><a class="booking-action" href="'.$edit_url.'" title="Edit this booking">';
				// $cell['body'] .= '<img alt="edit" src="' . $src . '" width="16" height="16" alt="Book" title="Edit" hspace="4" align="absmiddle" >';
				$cell['body'] .= ' edit </a>';
				$edit = 1;
			}

			// Cancel if user is an Admin, Room owner, or Booking owner
			$user_id = $this->userauth->user->user_id;
			if(
				($this->userauth->is_level(ADMINISTRATOR)) OR
				($user_id == $booking->user_id) OR
				( ($user_id == $rooms[$room_id]->user_id) && ($booking->date != NULL) )
			){
				$cancel_msg = 'Tem certeza de que deseja cancelar esta reserva?';
				if($user_id != $booking->user_id){
					$cancel_msg = 'Tem certeza de que deseja cancelar esta reserva?\n\n(**) Por favor, tenha cuidado, não é seu.';
				}
				$cancel_url = site_url('bookings/cancel/'.$booking->booking_id);
				if(!isset($edit)){ $cell['body'] .= '<br />'; }

				$src = base_url('assets/images/ui/delete.png');

				$cell['body'] .= '<button class="button-empty booking-action" type="submit" name="cancel" value="' . $booking->booking_id . '" onclick="if(!confirm(\''.$cancel_msg.'\')){return false;}">';
				$cell['body'] .= 'cancel';
				// $cell['body'] .= '<img alt="cancel" src="' . $src . '">';
				$cell['body'] .= '</button>';
				// $cell['body'] .= '<a onclick="if(!confirm(\''.$cancel_msg.'\')){return false;}" href="'.$cancel_url.'" title="Cancel this booking"><img src="' . base_url('assets/images/ui/delete.png') . '" width="16" height="16" alt="Cancel" title="Cancel this booking" hspace="8" /></a>';
			}

		}
		else
		{
			// No bookings
			$cell['class'] = 'free';
			$cell['body'] = '';

			$booking_status = $this->userauth->can_create_booking($booking_date_ymd);
			if ($booking_status->result === TRUE)
			{
				$book_url = site_url($url);
				$cell['class'] = 'free';
				$cell['body'] = '<a href="'.$book_url.'"><img src="' . base_url('assets/images/ui/accept.png') . '" width="16" height="16" alt="Reservar" title="Reservar" hspace="4" align="absmiddle" />Reservar</a>';
				if ($booking_status->is_admin)
				{
					$cell['body'] .= '<input type="checkbox" name="recurring[]" value="'.$url.'" />';
				}
			}


		}

		// If a holiday is applicable, display that instead.
		if (isset($holidays[$booking_date_ymd]))
		{
			$cell['class'] = 'holiday';
			$cell['body'] = $holidays[$booking_date_ymd][0]->name;
		}

	#$cell['width'] =
		#return sprintf('<td class="%s" valign="middle" align="center">%s</td>', $cell['class'], $cell['body']);
		return $this->load->view('bookings/table/bookingcell', $cell, True);
	}





	function html($params = array())
	{
		$defaults = array(
			'school' => array(),		// data loaded in controller (users, days)
			'query' => array(),		// input for where the user is/what should be loaded
		);

		$data = array_merge($defaults, $params);
		extract($data);

		// Format the date to Ymd
		if ( ! isset($query['date'])) {
			$date = time();
			$date_ymd = date("Y-m-d", $date);
		} else {
			$date = strtotime($query['date']);
			$date_ymd = date("Y-m-d", $date);
		}

		// Today's weekday number
		$day_num = date('N', $date);

		// Get info on the current week
		$this_week = $this->WeekObj($date);

		// Init HTML + Jscript variable
		$html = '';

		// Put users into array with their ID as the key
		foreach ($school['users'] as $user) {
			$users[$user->user_id] = $user;
		}

		// Get rooms
		$rooms = $this->Rooms();
		if ($rooms == FALSE) {
			$html .= msgbox('error', 'Nenhuma sala disponível.');
			return $html;
		}

		// Find out which columns to display and which view type we use
		$style = $this->BookingStyle();
		if ( ! $style OR (empty($style['cols']) OR empty($style['display']) ) ) {
			$html = msgbox('error', 'Nenhum estilo de reserva foi configurado. Entre em contato com seu administrador.');
			return $html;
		}
		$cols = $style['cols'];
		$display = $style['display'];

		// Select a default room if none given (first room)
		if ( ! isset($query['room'])) {
			$room_c = current($rooms);
			$query['room'] = $room_c->room_id;
		}

		// Load the appropriate select box depending on view style
		switch ($display) {

			case 'room':
				$html .= $this->load->view('bookings/select_room', array(
					'rooms' => $rooms,
					'room_id' => $query['room'],
					'chosen_date' => $date_ymd,
				), TRUE);
			break;

			case 'day':
				$html .= $this->load->view('bookings/select_date', array(
					'chosen_date' => $date,
				), TRUE);
			break;

			default:
				$html .= msgbox('error', 'Erro de aplicação: Sem tipo de display definido.');
				return $html;
			break;
		}

		$weekdates = array();
		$week_bar = array();

		// Change the week bar depending on view type
		switch ($display) {

			case 'room':

				$week_bar['back_date'] = date("Y-m-d", strtotime("last Week", $date));
				$week_bar['back_text'] = '&larr; Semana anterior';
				$week_bar['back_link'] = 'bookings?' . http_build_query(array(
					'date' => $week_bar['back_date'],
					'room' => $query['room'],
					'direction' => 'back',
				));

				$week_bar['next_date'] = date("Y-m-d", strtotime("next Week", $date));
				$week_bar['next_text'] = 'Próxima semana &rarr;';
				$week_bar['next_link'] = 'bookings?' . http_build_query(array(
					'date' => $week_bar['next_date'],
					'room' => $query['room'],
					'direction' => 'next',
				));

			break;

			case 'day':

				$week_bar['back_date'] = date("Y-m-d", strtotime("yesterday", $date));
				$week_bar['back_link'] = 'bookings?' . http_build_query(array(
					'date' => $week_bar['back_date'],
					'direction' => 'back',
				));

				$week_bar['next_date'] = date("Y-m-d", strtotime("tomorrow", $date));
				$week_bar['next_link'] = 'bookings?' . http_build_query(array(
					'date' => $week_bar['next_date'],
					'direction' => 'next',
				));

				if (date("Y-m-d") == date("Y-m-d", $date)) {
					$week_bar['back_text'] = '&larr; Ontem';
					$week_bar['next_text'] = 'Amanhã &rarr; ';
				} else {
					$week_bar['back_text'] = '&larr; Anterior';
					$week_bar['next_text'] = 'Próximo &rarr; ';
				}
				setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
				//date_default_timezone_set('America/Sao_Paulo');
				//print_r($date);die;
				$week_bar['longdate'] = ucfirst(utf8_encode(strftime('%A, %d de %B de %Y', $date)));//date(setting('date_format_long'), $date);

			break;
		}

		// Do we have any info on this week name?
		if ($this_week) {

			// Yes, so alter the week nav bar with the details of the week

			$week_bar['week_name'] = $this_week->name;

			// Get dates for each weekday
			if ($display == 'room') {

				$this_date = strtotime("-1 day", strtotime($this_week->date));
				foreach ($school['days_list'] as $d_day_num => $d_day_name) {
					$weekdates[$d_day_num] = date("Y-m-d", strtotime("+1 day", $this_date));
					$this_date = strtotime("+1 day", $this_date);
				}

				$week_bar['longdate'] = 'Week commencing '.date(setting('date_format_long'), strtotime($this_week->date));
			}

			$week_bar['style'] = sprintf('padding:6px 3px;font-weight:bold;background:#%s;color:#%s', $this_week->bgcol, $this_week->fgcol);

			$html .= $this->load->view('bookings/week_bar', $week_bar, TRUE);

		} else {

			// No week - change the properties to indicate no week available
			$week_bar['longdate'] = 'Week of '.date(setting('date_format_long'), $date);;
			$week_bar['week_name'] = 'None';
			$week_bar['style'] = sprintf('padding:6px 3px;font-weight:bold;background:#%s;color:#%s', 'dddddd', '000');
			$html .= $this->load->view('bookings/week_bar', $week_bar, TRUE);
			// Notify user no timetable week is available
			$html .= msgbox('error', 'No timetable week has been configured for this selection.');
			// Flag error to stop output before table
			$err = TRUE;

		}

		// Holidays
		//

		// Initialse sql to null here, so we can if it *isn't* later.
		// If it's not null, then we have SQL for holidays
		$sql = NULL;

		// See if our selected date is in a holiday
		if ($display === 'day')
		{
			// If we are day at a time, it is easy!
			// = get me any holidays where this day is anywhere in it
			$sql = "SELECT *
					FROM holidays
					WHERE date_start <= '{$date_ymd}'
					AND date_end >= '{$date_ymd}' ";
		}
		else
		{
			if ($this_week) {
				// If we are room/week at a time, little bit more complex
				$week_start = date('Y-m-d', strtotime($this_week->date));
				$week_end = date('Y-m-d', strtotime('+' . count($school['days_list']) . ' days', strtotime($this_week->date)));

				$sql = "SELECT *
						FROM holidays
						WHERE
						/* Starts before this week, ends this week */
						(date_start <= '$week_start' AND date_end <= '$week_end')
						/* Starts this week, ends this week */
						OR (date_start >= '$week_start' AND date_end <= '$week_end')
						/* Starts this week, ends after this week */
						OR (date_start >= '$week_start' AND date_end >= '$week_end')
						";
			}
		}

		$holidays = array();
		$holiday_dates = array();
		$holiday_interval = new DateInterval('P1D');

		if (isset($sql)) {
			$holiday_query = $this->db->query($sql);
			$holidays = $holiday_query->result();
		}

		// Organise our holidays by date
		foreach ($holidays as $holiday)
		{
			// Get all dates between date_start & date_end
			$start_dt = new DateTime($holiday->date_start);
			$end_dt = new DateTime($holiday->date_end);
			$end_dt->modify('+1 day');
			$range = new DatePeriod($start_dt, $holiday_interval, $end_dt);
			foreach ($range as $date)
			{
				$holiday_ymd = $date->format('Y-m-d');
				$holiday_dates[ $holiday_ymd ][] = $holiday;
			}
		}

		if ($display === 'day' && isset($holiday_dates[$date_ymd])) {

			// The date selected IS in a holiday - give them a nice message saying so.
			$holiday = $holiday_dates[ $date_ymd ][0];
			$msg = sprintf(
				'The date you selected is during a holiday priod (%s, %s - %s).',
				$holiday->name,
				date("d/m/Y", strtotime($holiday->date_start)),
				date("d/m/Y", strtotime($holiday->date_end))
			);
			$html .= msgbox('exclamation', $msg);

			// Let them choose the date afterwards/before
			// If navigating a day at a time, then just go one day.
			// If navigating one room at a time, move by one week
			if ($display === 'day') {
				$next_date = date("Y-m-d", strtotime("+1 day", strtotime($holiday->date_end)));
				$prev_date = date("Y-m-d", strtotime("-1 day", strtotime($holiday->date_start)));
			} elseif ($display === 'room') {
				$next_date = date("Y-m-d", strtotime("+1 week", strtotime($holiday->date_end)));
				$prev_date = date("Y-m-d", strtotime("-1 week", strtotime($holiday->date_start)));
			}

			if ( ! isset($query['direction'])) {
				$query['direction'] = 'forward';
			}

			switch ($query['direction']) {

				case 'forward':
					$query['date'] = $next_date;
					$uri = 'bookings?' . http_build_query($query);
					$link = anchor($uri, "Click here to view immediately after the holiday.");
					$html .= "<p><strong>{$link}</strong></p>";
				break;

				case 'back':
					$query['date'] = $prev_date;
					$uri = 'bookings?' . http_build_query($query);
					$link = anchor($uri, "Click here to view immediately before the holiday.");
					$html .= "<p><strong>{$link}</strong></p>";
				break;

			}

			$err = TRUE;
		}


		// Get periods
		if ($style['display'] == 'day') {
			$periods = $this->periods_model->GetBookable($day_num);
		} else {
			$periods = $this->periods_model->GetBookable();
		}

		if (empty($periods)) {
			$html .= msgbox('error', 'There are no periods configured or available for this day.');
			$err = TRUE;
		}

		if (isset($err) && $err == TRUE) {
			return $html;
		}

		$count = array(
			'periods' => count($periods),
			'rooms' => count($rooms),
			'days' => count($school['days_list']),
		);

		$col_width = sprintf('%s%%', round(100/($count[$cols]+1)));

		// Open form
		$html .= form_open('bookings/action', array(
			'name' => 'bookings',
		));
		$html .= form_hidden('room_id', $query['room']);

		// Here goes, start table
		$html .= '<table border="0" bordercolor="#ffffff" cellpadding="2" cellspacing="2" class="bookings" width="100%">';

		// COLUMNS !!
		$html .= '<tr><td>&nbsp;</td>';

		switch ($cols) {

			case 'periods':

				foreach ($periods as $period) {
					$period->width = $col_width;
					$html .= $this->load->view('bookings/table/cols_periods', $period, TRUE);
				}

			break;

			case 'days':

				foreach ($school['days_list'] as $day_num => $dayofweek) {
					$day['width'] = $col_width;
					$day['name'] = $dayofweek;
					$day['date'] = $weekdates[$day_num];
					$html .= $this->load->view('bookings/table/headings/days', $day, TRUE);
				}

			break;

			case 'rooms':

				foreach ($rooms as $room) {
					$room->width = $col_width;
					$html .= $this->load->view('bookings/table/cols_rooms', $room, TRUE);
				}

			break;

		}	// End switch for cols

		$bookings = array();

		// Here we go!
		switch ($display) {

			case 'room':

				// ONE ROOM AT A TIME - COLS ARE PERIODS OR DAY NAMES...

				switch ($cols) {

					case 'periods':

						/*
							    [P1] [P2] [P3] ...
							[Mo]
							[Tu]
							....
						*/

						// Columns are periods, so each row is a day name

						foreach ($school['days_list'] as $day_num => $day_name) {

							// Get booking
							// TODO: Need to get date("Y-m-d") of THIS weekday (Mon, Tue, Wed) for this week
							$bookings = array();

							$sql = "SELECT * FROM bookings
									WHERE room_id = ?
									AND ((day_num = ? AND week_id = ?) OR `date` = ?) ";

							$bookings_query = $this->db->query($sql, array(
								$query['room'],
								$day_num,
								$this_week->week_id,
								$weekdates[$day_num],
							));

							if ($bookings_query->num_rows() > 0) {
								$result = $bookings_query->result();
								foreach ($result as $row) {
									$bookings[$row->period_id] = $row;
								}
							}

							$bookings_query->free_result();

							$booking_date_ymd = $weekdates[$day_num];

							// Start row
							$html .= '<tr>';

							// First cell
							$day['width'] = $col_width;
							$day['name'] = $day_name;
							$day['date'] = $booking_date_ymd;
							$html .= $this->load->view('bookings/table/rowinfo/days', $day, TRUE);


							// Now all the other ones to fill in periods
							foreach ($periods as $period) {

								// URL
								$book_url_query = array(
									'period' => $period->period_id,
									'room' => $query['room'],
									'day_num' => $day_num,
									'week' => $this_week->week_id,
									'date' => $booking_date_ymd,
								);
								$url = 'bookings/book?' . http_build_query($book_url_query);

								// This period is bookable on this day?
								$key = "day_{$day_num}";
								if ($period->{$key} == '1') {
									// Bookable
									$html .= $this->BookingCell($bookings, $period->period_id, $rooms, $users, $query['room'], $url, $booking_date_ymd, $holiday_dates);
								} else {
								// Period not bookable on this day, do not show or allow any bookings
									$html .= '<td align="center">&nbsp;</td>';
								}

							}		// Done looping periods (cols)

							// This day row is finished
							$html .= '</tr>';

						}


					break;		// End $display 'room' $cols 'periods'

					case 'days':

						/*
								 [Mo] [Tu] [We] ...
							[P1]
							[P2]
							....
						*/

						// Columns are days, so each row is a period

						foreach ($periods as $period) {

							// Get bookings
							$bookings = array();
							$sql = "SELECT * FROM bookings
									WHERE room_id = ?
									AND period_id = ?
									AND ( week_id = ? OR (`date` >= ? AND `date` <= ?) )";
									#."AND ((day_num=$day_num AND week_id=$this_week->week_id) OR date='$date_ymd') ";

							$bookings_query = $this->db->query($sql, array(
								$query['room'],
								$period->period_id,
								$this_week->week_id,
								$weekdates[1],
								$weekdates[7],
							));

							$results = $bookings_query->result();
							if ($bookings_query->num_rows() > 0) {
								foreach ($results as $row) {
									if ( ! empty($row->date)) {
										// Static booking on date
										$this_daynum = date('N', strtotime($row->date));
										$bookings[$this_daynum] = $row;
									} else {
										// Recurring booking
										$bookings[$row->day_num] = $row;
									}
								}
							}
							$bookings_query->free_result();

							// Start row
							$html .= '<tr>';

							// First cell, info
							$period->width = $col_width;
							$html .= $this->load->view('bookings/table/rows_periods', $period, TRUE);

							foreach ($school['days_list'] as $day_num => $day_name) {

								$booking_date_ymd = $weekdates[$day_num];

								// URL
								$book_url_query = array(
									'period' => $period->period_id,
									'room' => $query['room'],
									'day_num' => $day_num,
									'week' => $this_week->week_id,
									'date' => $booking_date_ymd,
								);
								$url = 'bookings/book?' . http_build_query($book_url_query);

								// $url = 'period/%s/room/%s/day/%s/week/%s/date/%s';
								// $url = sprintf($url, $period->period_id, $room_id, $day_num, $this_week->week_id, $booking_date_ymd);

								// Is this period bookable on this day?
								$key = "day_{$day_num}";
								if ($period->{$key} == '1') {
									// Bookable
									$html .= $this->BookingCell($bookings, $day_num, $rooms, $users, $query['room'], $url, $booking_date_ymd, $holiday_dates);
								} else {
									// Period not bookable on this day, do not show or allow any bookings
									$html .= '<td align="center">&nbsp;</td>';
								}

							}

							// This period row is finished
							$html .= '</tr>';

						}

					break;		// End $display 'room' $cols 'days'

				}

			break;

			case 'day':

				// ONE DAY AT A TIME - COLS ARE DAY NAMES OR ROOMS

				switch ($cols) {

					case 'periods':

						/*
								[P1] [P2] [P3] ...
							[R1]
							[R2]
							....
						*/

						// Columns are periods, so each row is a room

						foreach ($rooms as $room) {

							$bookings = array();

							// See if there are any bookings for any period this room.
							// A booking will either have a date (teacher booking), or a day_num and week_id (static/timetabled)

							$sql = "SELECT *
									FROM bookings
									WHERE room_id = ?
									AND ((day_num = ? AND week_id = ?) OR `date` = ?)";

							$bookings_query = $this->db->query($sql, array(
								$room->room_id,
								$day_num,
								$this_week->week_id,
								$date_ymd,
							));

							if ($bookings_query->num_rows() > 0){
								$result = $bookings_query->result();
								foreach ($result as $row) {
									$bookings[$row->period_id] = $row;
								}
							}
							$bookings_query->free_result();

							// Start row
							$html .= '<tr>';

							$room->width = $col_width;
							$html .= $this->load->view('bookings/table/rows_rooms', $room, TRUE);

							foreach ($periods as $period) {

								// URL
								$book_url_query = array(
									'period' => $period->period_id,
									'room' => $room->room_id,
									'day_num' => $day_num,
									'week' => $this_week->week_id,
									'date' => $date_ymd,
								);
								$url = 'bookings/book?' . http_build_query($book_url_query);

								$key = "day_{$day_num}";
								if ($period->{$key} == '1') {
									// Bookable
									$html .= $this->BookingCell($bookings, $period->period_id, $rooms, $users, $room->room_id, $url, $date_ymd, $holiday_dates);
								} else {
									// Period not bookable on this day, do not show or allow any bookings
									$html .= '<td align="center">&nbsp;</td>';
								}
							}

							// End row
							$html .= '</tr>';

						}

					break;		// End $display 'day' $cols 'periods'

					case 'rooms':

						/*
							[R1] [R2] [R3] ...
						[P1]
						[P2]
						*/

						// Columns are rooms, so each row is a period

						foreach ($periods as $period) {

							$bookings = array();

							// See if there are any bookings for any period this room.
							// A booking will either have a date (teacher booking), or a day_num and week_id (static/timetabled)
							$sql = "SELECT * FROM bookings
									WHERE period_id = ?
									AND ((day_num = ? AND week_id = ?) OR `date` = ?) ";

							$bookings_query = $this->db->query($sql, array(
								$period->period_id,
								$day_num,
								$this_week->week_id,
								$date_ymd,
							));

							if ($bookings_query->num_rows() > 0) {
								$result = $bookings_query->result();
								foreach ($result as $row){
									$bookings[$row->room_id] = $row;
								}
							}

							$bookings_query->free_result();

							// Start period row
							$html .= '<tr>';

							// First cell, info
							$period->width = $col_width;
							$html .= $this->load->view('bookings/table/rows_periods', $period, TRUE);

							foreach ($rooms as $room) {

								// URL
								$book_url_query = array(
									'period' => $period->period_id,
									'room' => $room->room_id,
									'day_num' => $day_num,
									'week' => $this_week->week_id,
									'date' => $date_ymd,
								);
								$url = 'bookings/book?' . http_build_query($book_url_query);

								// $url = 'period/%s/room/%s/day/%s/week/%s/date/%s';
								// $url = sprintf($url, $period->period_id, $room->room_id, $day_num, $this_week->week_id, $date_ymd);

								// Bookable on this day?
								$key = "day_{$day_num}";
								if ($period->{$key} == '1') {
									// Bookable
									$html .= $this->BookingCell($bookings, $room->room_id, $rooms, $users, $room->room_id, $url, $date_ymd, $holiday_dates);
								} else {
									// Period not bookable on this day, do not show or allow any bookings
									$html .= '<td align="center">&nbsp;</td>';
								}
							}

							// End period row
							$html .= '</tr>';

						}

					break;		// End $display 'day' $cols 'rooms'

				}

			break;

		}


		$html .= $this->Table();

		// Finish table
		$html .= '</table>';

		// Visual key
		$html .= $this->load->view('bookings/key', NULL, TRUE);

		// Show link to making a booking for admins
		if ($this->userauth->is_level(ADMINISTRATOR)) {
			$html .= $this->load->view('bookings/make_recurring', array('users' => $school['users']), TRUE);
		}

		$html .= form_close();

		// Finaly return the HTML variable so the controller can then pass it to the view.
		return $html;
	}




	public function Cancel($booking_id)
	{
		$sql = "DELETE FROM bookings
				WHERE booking_id = ?
				LIMIT 1";

		$query = $this->db->query($sql, array($booking_id));
		return ($query && $this->db->affected_rows() == 1);
	}




	function BookingStyle()
	{
		$out = array(
			'cols' => setting('d_columns'),
			'display' => setting('displaytype'),
		);

		if (empty($out['cols']) || empty($out['display'])) {
			return FALSE;
		}

		return $out;
	}




	/**
	 * Get rooms and their users
	 *
	 */
	function Rooms()
	{
		$sql = "SELECT rooms.*, users.user_id, users.username, users.displayname
				FROM rooms
				LEFT JOIN users ON users.user_id=rooms.user_id
				WHERE rooms.bookable=1
				ORDER BY name asc";

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			$result = $query->result();
			// Put all room data into an array where the key is the room_id
			foreach ($result as $room) {
				$rooms[$room->room_id] = $room;
			}
			return $rooms;
		}

		return FALSE;
	}




	/**
	 * Returns an object containing the week information for a given date
	 *
	 */
	public function WeekObj($date)
	{
		// First find the monday date of the week that $date is in
		if (date("N", $date) == 1) {
			$nextdate = date("Y-m-d", $date);
		} else {
			$nextdate = date("Y-m-d", strtotime("last Monday", $date));
		}

		// Get week info that this date falls into
		$sql = "SELECT * FROM weeks, weekdates
				WHERE weeks.week_id = weekdates.week_id
				AND weekdates.date = '$nextdate'
				LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows() == 1) {
			$row = $query->row();
		} else {
			$row = false;
		}

		return $row;
	}




	/**
	 * Add a booking
	 *
	 */
	function Add($data = array())
	{
		// Run query to insert blank row
		$this->db->insert('bookings', array('booking_id' => NULL));
		// Get id of inserted record
		$booking_id = $this->db->insert_id();
		// Now call the edit function to update the actual data for this new row now we have the ID
		return $this->Edit($booking_id, $data);
	}




	function Edit($booking_id, $data)
	{
		$this->db->where('booking_id', $booking_id);
		$result = $this->db->update('bookings', $data);
		// Return bool on success
		if ($result) {
			return $booking_id;
		} else {
			return false;
		}
	}




	function ByRoomOwner($user_id = 0)
	{
		$maxdate = date("Y-m-d", strtotime("+14 days", Now()));
		$today = date("Y-m-d");
		$sql = "SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='$user_id' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '$maxdate'
				AND bookings.date >= '$today'
				ORDER BY bookings.date, rooms.name ";

		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			// We have some bookings
			return $query->result();
		}

		return FALSE;
	}




	function ByUser($user_id)
	{
		$maxdate = date("Y-m-d", strtotime("+14 days", Now()));
		$today = date("Y-m-d");
		// All current bookings for this user between today and 2 weeks' time
		$sql = "SELECT rooms.*, bookings.*, periods.name as periodname, periods.time_start, periods.time_end
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE bookings.user_id='$user_id' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '$maxdate'
				AND bookings.date >= '$today'
				ORDER BY bookings.date asc, periods.time_start asc";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	public function CountScheduledByUser($user_id)
	{
		$today = date("Y-m-d");
		$time = date('H:i');

		$sql = 'SELECT COUNT(booking_id) AS total
				FROM bookings
				JOIN periods ON periods.period_id = bookings.period_id
				WHERE bookings.user_id = ?
				AND bookings.cancelled = 0
				AND bookings.date IS NOT NULL
				AND (
					(bookings.date > ?)	/* after today */
					OR
					(bookings.date = ? AND periods.time_start > ?) /* today, but after cur time */
				)';

		$query = $this->db->query($sql, [
			$user_id,
			$today,
			$today,
			$time
		]);

		$row = $query->row_array();
		return (int) $row['total'];
	}


	function TotalNum($user_id = 0)
	{
		$today = date("Y-m-d");

		// All bookings by user, EVER!
		$sql = "SELECT COUNT(booking_id) AS total
				FROM bookings
				WHERE user_id = ?";
		$query = $this->db->query($sql, [$user_id]);
		$row = $query->row_array();
		$total['all'] = $row['total'];

		// All bookings by user, for this academic year, up to and including today
		$sql = "SELECT COUNT(booking_id) AS total
				FROM bookings
				JOIN academicyears ON bookings.date >= academicyears.date_start
				WHERE bookings.user_id = ? ";
		$query = $this->db->query($sql, [$user_id]);
		$row = $query->row_array();
		$total['yeartodate'] = $row['total'];

		// All bookings up to and including today
		$sql = "SELECT COUNT(booking_id) AS total
				FROM bookings
				WHERE bookings.user_id = ?
				AND bookings.date <= ?";
		$query = $this->db->query($sql, [$user_id, $today]);
		$row = $query->row_array();
		$total['todate'] = $row['total'];

		$total['active'] = $this->CountScheduledByUser($user_id);

		return $total;
	}




}
