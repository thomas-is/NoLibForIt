#!/bin/bash

for FILE in $(ls)
do
  if [ -f "./$FILE/.git" ]; then
    echo "$FILE"
    cd ./$FILE
    git pull
    cd ..
  fi
done
