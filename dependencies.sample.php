<?php
use Pimple\Container;
use Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
use \Ois\WpCliImport\FireflyPageImporter;

$dependencies = new Container();

$dependencies['dbConfig'] = function ($c) {

    new Configuration;
};

$dependencies['dbParams'] = array(
    'dbname'   => 'mydb',
    'user'     => 'user',
    'password' => 'secret',
    'host'     => 'localhost',
    'driver'   => 'pdo_mysql',
);

$dependencies['connection'] = function ($c) {

    return DriverManager::getConnection($c['dbParams'], $c['dbConfig']);
};

$dependencies['FireflyPageImporter'] = function ($c) {

    return new FireflyPageImporter(
        $c['connection'],
        'vendor/wp-cli/wp-cli/bin/wp'
    );
};