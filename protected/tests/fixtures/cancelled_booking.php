<?php
/*
_____________________________________________________________________________
(C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
(C) OpenEyes Foundation, 2011
This file is part of OpenEyes.
OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
_____________________________________________________________________________
http://www.openeyes.org.uk   info@openeyes.org.uk
--
*/


return array(
	'booking1' => array(
		'element_operation_id' => 1,
		'date' => date('Y-m-d'),
		'start_time' => '09:00',
		'end_time' => '13:00',
		'theatre_id' => 1,
		'cancelled_date' => date('Y-m-d', strtotime('-7 days')),
		'user_id' => 1,
		'cancelled_reason_id' => 1
	),
	'booking2' => array(
		'element_operation_id' => 2,
		'date' => date('Y-m-d', strtotime('-2 days')),
		'start_time' => '13:30',
		'end_time' => '18:00',
		'theatre_id' => 2,
		'cancelled_date' => date('Y-m-d', strtotime('-30 days')),
		'user_id' => 2,
		'cancelled_reason_id' => 2,
	),
);