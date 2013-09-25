<div class="box admin">
	<h2>Core</h2>
	<ul class="navigation admin">
		<?php foreach (array(
			'Users' => '/admin/users',
			'Firms' => '/admin/firms',
			'Contacts' => '/admin/contacts',
			'Contact labels' => '/admin/contactlabels',
			'Data sources' => '/admin/datasources',
			'Institutions' => '/admin/institutions',
			'Sites' => '/admin/sites',
			'Commissioning bodies' => '/admin/commissioning_bodies',
			'Commissioning body types' => '/admin/commissioning_body_types',
			'Commissioning body services' => '/admin/commissioning_body_services',
			'Commissioning body service types' => '/admin/commissioning_body_service_types',
			/*
			'Global phrases' => '/admin/globalPhrases',
			'Phrases by subspecialty' => '/admin/phrasesBySubspecialty',
			'Phrases by firm' => '/admin/phrasesByFirm',
			'Letter templates' => '/admin/letterTemplates',
			'Sequences' => '/admin/sequences',
			'Sessions' => '/admin/sessions',
			'Episode status' => '/admin/episodeStatus',
			*/
		) as $title => $uri) {?>
			<li<?php if (Yii::app()->getController()->action->id == preg_replace('/^\/admin\//','',$uri)) {?> class="selected"<?php }?>>
				<?php if (Yii::app()->getController()->action->id == preg_replace('/^\/admin\//','',$uri)) {?>
					<?php echo CHtml::link($title,array($uri),array('class' => 'selected'))?>
				<?php } else {?>
					<?php echo CHtml::link($title,array($uri))?>
				<?php }?>
			</li>
		<?php }?>
	</ul>
</div>
<?php foreach (ModuleAdmin::getAll() as $module => $items) {?>
	<div class="admin box">
		<h2><?php echo $module?></h2>
		<ul class="navigation admin">
			<?php foreach ($items as $item => $uri) {
				$e = explode('/',$uri);
				$action = array_pop($e)?>
				<li<?php if (Yii::app()->getController()->action->id == $action) {?> class="selected"<?php }?>>
					<?php if (Yii::app()->getController()->action->id == $action) {?>
						<span class="viewing"><?php echo $item?></span>
					<?php } else {?>
						<?php echo CHtml::link($item,array($uri))?>
					<?php }?>
				</li>
			<?php }?>
		</ul>
	</div>
<?php }?>
