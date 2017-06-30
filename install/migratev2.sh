#!/bin/bash

printf "\e[31mMIGRATING ALLERGIES TO V2\e[0m\n"
/var/www/openeyes/protected/yiic allergymigrate
printf "\e[31mMIGRATING FAMILY HISTORY TO V2\e[0m\n"
/var/www/openeyes/protected/yiic familyhistorymigrate
printf "\e[31mMIGRATING PAST SURGERY TO V2\e[0m\n"
/var/www/openeyes/protected/yiic pastsurgerymigrate
printf "\e[31mMIGRATING SOCIAL HISTORY TO V2\e[0m\n"
/var/www/openeyes/protected/yiic socialhistorymigrate
