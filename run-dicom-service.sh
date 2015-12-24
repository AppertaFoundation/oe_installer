#!/bin/bash

# Retrieve the file_watcher path from the /etc/openeyes/file_watcher.conf file
# If unable to, then set it to the default of /var/www/openeyes/protected/cli_commands/file_watcher

defaultcmdpath='/var/www/openeyes/protected/cli_commands/file_watcher';
cmdpath=`grep PHPdir /etc/openeyes/file_watcher.conf | cut -d"'" -f2`
if [ "$cmdpath" = "" ]; then
  cmdpath=$defaultcmdpath;
fi

cd $cmdpath && /usr/bin/php -f runFileWatcher.php
