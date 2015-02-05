<?php
use Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;

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