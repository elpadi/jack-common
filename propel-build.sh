#!/bin/sh

### Update ###
rm -f config/propel/propel.json.dist
rm -f config/propel/propel.php
vendor/propel/propel/bin/propel sql:build --mysql-engine="InnoDB" --config-dir config/propel --schema-dir="config/propel" --output-dir="config/propel" --overwrite
vendor/propel/propel/bin/propel model:build --config-dir config/propel --schema-dir config/propel --output-dir src
vendor/propel/propel/bin/propel config:convert --config-dir config/propel --output-dir config/propel --output-file propel.php
### End Update ###

### Reset ###
# vendor/propel/propel/bin/propel sql:insert --sql-dir="config/propel"
### End Reset ###
