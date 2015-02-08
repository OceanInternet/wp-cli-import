<?php
    use \Ois\FireflyImport\FireflyEditorial;
    use \Ois\FireflyImport\FireflyNews;
    use \Ois\FireflyImport\FireflyRegions;
    use \Ois\FireflyImport\FireflyVenues;
    use \Ois\FireflyImport\FireflyFixtures;
    use \Ois\FireflyImport\FireflyClubs;
    use \Ois\FireflyImport\FireflyBoats;
    use \Ois\FireflyImport\FireflyResults;
    use \Ois\FireflyImport\FireflyUsers;
    use \Ois\FireflyImport\FireflyLibraries;

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

        return new FireflyVenues(
            $c['connection'],
            $c['FireflyFixtures'],
            $c['FireflyClubs'],
            $c['wp-cli']
        );
    };

    $dependencies['FireflyFixtures'] = function ($c) {

        return new FireflyFixtures(
            $c['connection'],
            $c['wp-cli']
        );
    };

    $dependencies['FireflyClubs'] = function ($c) {

        return new FireflyClubs(
            $c['connection'],
            $c['FireflyNews'],
            $c['FireflyBoats'],
            $c['wp-cli']);
    };

    $dependencies['FireflyBoats'] = function ($c) {

        return new FireflyBoats(
            $c['connection'],
            $c['FireflyResults'],
            $c['wp-cli']
        );
    };

    $dependencies['FireflyResults'] = function ($c) {

        return new FireflyResults(
            $c['connection'],
            $c['wp-cli']
        );
    };

    $dependencies['FireflyUsers'] = function ($c) {

        return new FireflyUsers(
            $c['connection'],
            $c['wp-cli']
        );
    };

    $dependencies['FireflyLibraries'] = function ($c) {

        return new FireflyLibraries(
            $c['connection'],
            $c['wp-cli']
        );
    };
