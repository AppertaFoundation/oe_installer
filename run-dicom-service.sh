#!/bin/bash

# Retrieve the file_watcher path from the /etc/openeyes/file_watcher.conf file
# If unable to, then set it to the default of /var/www/openeyes/protected/cli_commands/file_watcher

$cmdpath = '/var/www/openeyes/protected/cli_commands/file_watcher';
$line =`grep PHPdir /etc/openeyes/file_watcher.conf`
echo $line
exit


cd $cmdpath && /usr/bin/php -f runFileWatcher.php
