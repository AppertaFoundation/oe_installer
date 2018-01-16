#!/bin/bash

printf "\e[33mMIGRATING ALLERGIES TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic allergymigrate
printf "\e[33mMIGRATING FAMILY HISTORY TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic familyhistorymigrate
printf "\e[33mMIGRATING PAST SURGERY TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic pastsurgerymigrate
printf "\e[33mMIGRATING SOCIAL HISTORY TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic socialhistorymigrate
printf "\e[33mMIGRATING RISKS TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic risksmigrate
printf "\e[33mMIGRATING MEDICATIONS TO V2\e[0m\n"
php /var/www/openeyes/protected/yiic medicationmigrate
