<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2020-07-30 08:07:55 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='1' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 08:07:55 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 08:23:08 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='1' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 08:23:08 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 13:12:36 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='6' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 13:12:36 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 13:12:38 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='6' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 13:12:38 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 13:12:38 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='6' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 13:12:38 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 14:57:43 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='1' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 14:57:43 --> Severity: error --> Exception: Call to a member function num_rows() on boolean /var/www/html/salas/application/models/Bookings_model.php 1094
ERROR - 2020-07-30 14:58:53 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/salas/application/vendor/codeigniter/framework/system/database/drivers/mysqli/mysqli_driver.php 120
ERROR - 2020-07-30 14:58:53 --> Query error: Unknown column 'bookings.cancelled' in 'where clause' - Invalid query: SELECT rooms.*, bookings.*, users.username, users.displayname, users.user_id, periods.name as periodname
				FROM bookings
				JOIN rooms ON rooms.room_id=bookings.room_id
				JOIN users ON users.user_id=bookings.user_id
				JOIN periods ON periods.period_id=bookings.period_id
				WHERE rooms.user_id='1' AND bookings.cancelled=0
				AND bookings.date IS NOT NULL
				AND bookings.date <= '2020-08-13'
				AND bookings.date >= '2020-07-30'
				ORDER BY bookings.date, rooms.name 
ERROR - 2020-07-30 15:01:56 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/salas/application/vendor/codeigniter/framework/system/database/drivers/mysqli/mysqli_driver.php 120
