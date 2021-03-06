#!/bin/bash

#script to check if all dialogs are used

#check input
if (( $# < 1 )); then echo "Usage: $0 [language_file.json]";exit;fi

#get file name
file=$1

#loop text ids
grep '#' $file | awk '{print $1}' | cut -d\" -f2 | cut -d\# -f2 | while read -r id ; do

	#debug 
	#echo Checking: $id ...

	id=$(echo $id | sed "s/_descr//g")
	id=$(echo $id | sed "s/_expla//g")

	#coincidences
	co=$(grep $id ../* | wc -l)

	(( co+=$(grep $id ../dataModel/* | wc -l) ))

	if (($co == 0 ))
		then echo $id : $co coincidences
	fi
done
