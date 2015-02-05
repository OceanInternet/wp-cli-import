<?php
use \Ois\FireflyImport\FireflyEditorial;
use \Ois\FireflyImport\FireflyNews;
use \Ois\FireflyImport\FireflyRegions;

$dependencies['wp-cli'] = 'vendor/wp-cli/wp-cli/bin/wp';

$dependencies['FireflyEditorial'] = function ($c) {

    return new FireflyEditorial(
        $c['connection'],
        $c['wp-cli']
    );
};

$dependencies['FireflyNews'] = function ($c) {

    return new FireflyNews(
        $c['connection'],
        $c['wp-cli']
    );
};

$dependencies['FireflyRegions'] = function ($c) {

    return new FireflyRegions(
        $c['connection'],
        $c['wp-cli']
    );
};
