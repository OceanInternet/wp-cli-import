# wp-cli-import

## Install

```
composer install
cp connections.sample.php connections.php
```

Setup your db connections in connections.php

## Create an Importer

* Extend WpCliImport and implement import() method (see FireflyImporter for an example)
* Add you new class to dependencies.php

## Import All The Things

```
./import MyNewClass
```

This will create an instance of MyNewClass and run MyNewClass::import()
