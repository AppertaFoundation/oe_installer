<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
?>
<ul class="pagination right">
	<?php
	if (@$onePageOnly && $page >10) {?>
		<li><a href="<?php echo Yii::app()->createUrl($this->getUri(array('page'=>$page-10)))?>">&laquo;10</a></li>
	<?php }
	if ($page > 1) {?>
		<li><a href="<?php echo Yii::app()->createUrl($this->getUri(array('page'=>$page-1)))?>">&laquo; back</a></li>
	<?php } else {?>
		<li class="unavailable"><a href="">&laquo; back</a></li>
	<?php }?>
	<?php for ($i=1; $i<=$pages;$i++) {?>
		<?php if ($i == $page) {?>
			<li class="current"><a href=""><?php echo $i?></a></li>
		<?php } else {?>
			<?php if (!@$onePageOnly) {?>
				<li><a href="<?php echo Yii::app()->createUrl($this->getUri(array('page'=>$i)))?>"><?php echo $i?></a></li>
			<?php }?>
		<?php }?>
	<?php }?>
	<?php if ($page < $pages) {?>
		<li><a href="<?php echo Yii::app()->createUrl($this->getUri(array('page'=>$page+1)))?>">next &raquo;</a></li>
	<?php } else {?>
		<li class="unavailable"><a href="">next &raquo;</a></li>
	<?php }
	if (@$onePageOnly && $page < $pages-9) {?>
	  <li><a href="<?php echo Yii::app()->createUrl($this->getUri(array('page'=>$page+10)))?>">10 &raquo;</a></li>
	<?php }
	if (@$onePageOnly) {?>
		<li><?php echo $page?>/<?php echo $pages?></li>
	<?php }?>
</ul>
