<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */


return array(
	'event1' => array(
		'episode_id' => 1,
		'created_user_id' => 1,
		'event_type_id' => 1,
		'last_modified_user_id'=>1,
		'last_modified_date' => date('Y-m-d 00:00:00'),
		'created_date' => date('Y-m-d 00:00:00'),
		'accomplished_date' => null,
		'info'=>'someinfo',
		'deleted'=> false,
		'delete_reason' => null,
		'delete_pending'=> false,
	),
	'event2' => array(
		'episode_id' => 2,
		'created_user_id' => 1,
		'event_type_id' => 1,
		'last_modified_user_id'=>1,
		'last_modified_date' => date('Y-m-d 01:00:00'),
		'created_date' => date('Y-m-d 00:00:00'),
		'accomplished_date' => date('Y-m-d 00:00:00', strtotime('-2 days')),
		'info'=>'someinfo2',
		'deleted'=> false,
		'delete_reason' => null,
		'delete_pending'=> false,
	),
	'event3' => array(
		'episode_id' => 2,
		'created_user_id' => 1,
		'event_type_id' => 1,
		'last_modified_user_id'=>1,
		'last_modified_date' => date('Y-m-d 00:00:00'),
		'created_date' => date('Y-m-d 01:00:00'),
		'accomplished_date' => null,
		'info'=>'someinfo3',
		'deleted'=> false,
		'delete_reason' => null,
		'delete_pending'=> false,
	),
	'event4' => array(
		'episode_id' => 3,
		'created_user_id' => 1,
		'event_type_id' => 1,
		'last_modified_user_id'=>1,
		'last_modified_date' => date('Y-m-d 00:00:00'),
		'created_date' => date('Y-m-d 00:00:00'),
		'accomplished_date' => null,
		'info'=>'someinfo',
		'deleted'=> false,
		'delete_reason' => null,
		'delete_pending'=> false,
	),
	'event5' => array(
		'episode_id' => 4,
		'created_user_id' => 1,
		'event_type_id' => 1,
		'last_modified_user_id'=>1,
		'last_modified_date' => date('Y-m-d 00:00:00'),
		'created_date' => date('Y-m-d 00:00:00'),
		'accomplished_date' => null,
		'info'=>'someinfo',
		'deleted'=> false,
		'delete_reason' => null,
		'delete_pending'=> false,
	),
);
