class core::php5 {
	package { 'php5':
		ensure  => present,
		require => Exec['apt-update'],
		notify  => Service['apache2']
	}

	package { 'php5-cli':
		ensure  => present,
		require => Exec['apt-update']
	}

	package { 'libapache2-mod-php5':
		ensure  => present,
		require => Exec['apt-update'],
		notify  => Service['apache2']
	}

	package { 'php5-curl':
		ensure  => present,
		require => Exec['apt-update'],
		notify  => Service['apache2']
	}

	package { 'php5-curl':
		ensure  => present,
		require => [ Exec['apt-update'], Package['php5', 'php5-cli']],
		notify  => Service['apache2']
	}

	package { 'php5-imagick':
		ensure  => present,
		require => [ Exec['apt-update'], Package['php5', 'php5-cli']],
		notify  => Service['apache2']
	}

	package { 'php5-gd':
		ensure  => present,
		require => [ Exec['apt-update'], Package['php5', 'php5-cli']],
		notify  => Service['apache2']
	}

	package { 'imagemagick':
		ensure  => present,
		require => Exec['apt-update'],
		notify  => Service['apache2']
	}

	file {'/etc/php5/cli/conf.d/buffering_settings.ini':
		ensure => present,
		owner => root, group => root, mode => 444,
		content => "output_buffering = On \nzend.enable_gc = 0 \ndate.timezone = Europe/London",
		require => Package['php5-cli']
	}
}