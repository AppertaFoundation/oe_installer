<?php
/**
 * (C) OpenEyes Foundation, 2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */


class AutoSave
{
	public static function get($key)
	{
		if($auto_save = Yii::app()->session['autosave']){
			if(isset($auto_save[$key])){
				return $auto_save[$key];
			}
		}
		return;
	}

	public static function add($key, $data)
	{
		$auto_save = Yii::app()->session['autosave'];
		$auto_save[$key] = $data;
		Yii::app()->session['autosave'] = $auto_save;
	}

	public static function remove($key)
	{
		$auto_save = Yii::app()->session['autosave'];
		unset($auto_save[$key]);
		Yii::app()->session['autosave'] = $auto_save;
	}

	public static function removeAllByPrefix($prefix)
	{
		if($auto_save = Yii::app()->session['autosave'])
		{
			foreach ($auto_save as $key => $value){
				if(substr($key,0,strlen($prefix))===$prefix){
					unset($auto_save[$key]);
				}
			}
			Yii::app()->session['autosave'] = $auto_save;
		}
	}
}