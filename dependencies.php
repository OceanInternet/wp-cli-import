<?php
    use \Ois\FireflyImport\FireflyEditorial;
    use \Ois\FireflyImport\FireflyNews;
    use \Ois\FireflyImport\FireflyRegions;
    use \Ois\FireflyImport\FireflyVenues;
    use \Ois\FireflyImport\FireflyFixtures;

    $dependencies['wp-cli'] = 'vendor/wp-cli/wp-cli/bin/wp';

    $dependencies['FireflyEditorial'] = function ($c) {

        return new FireflyEditorial($c['connection'], $c['wp-cli']);
    };

    $dependencies['FireflyNews'] = function ($c) {

        return new FireflyNews($c['connection'], $c['wp-cli']);
    };

    $dependencies['FireflyRegions'] = function ($c) {

        return new FireflyRegions($c['connection'], $c['wp-cli']);
    };

    $dependencies['FireflyVenues'] = function ($c) {

        return new FireflyVenues($c['connection'], $c['FireflyFixtures'], $c['wp-cli']);
    };

    $dependencies['FireflyFixtures'] = function ($c) {

        return new FireflyFixtures($c['connection'], $c['wp-cli']);
    };
