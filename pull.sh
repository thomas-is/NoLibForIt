#!/bin/bash

for FILE in $(ls)
do
  if [ -d $FILE ]; then
    echo "$FILE"
    cd ./$FILE
    git pull
    cd ..
  fi
done
