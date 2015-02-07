<?php
    namespace Ois\FireflyImport;

    use Doctrine\DBAL\Connection;

    class FireflyVenues extends FireflyImport
    {

        /**
         * @var FireflyFixtures
         */
        protected $FireflyFixtures;

        /**
         * @var FireflyClubs
         */
        protected $FireflyClubs;

        protected $name       = 'Firefly Clubs (venues)';
        protected $type       = 'tribe_venue';
        protected $table      = 'club';
        protected $slug       = 'club';
        protected $titleField = 'club';

        protected $postConditions = array();

        public function __construct(
            Connection $connection,
            FireflyFixtures $FireflyFixtures,
            FireflyClubs    $FireflyClubs,
            $wpCli = 'wp', Array $wpCliArgs = array(), $encoding = 'UTF-8')
        {

            parent::__construct($connection, $wpCli, $wpCliArgs, $encoding);

            $this->FireflyFixtures = $FireflyFixtures;
            $this->FireflyClubs    = $FireflyClubs;

        }

        public function import()
        {

            //parent::import();

            echo 'running Firefly Venues import';

            $this->FireflyFixtures->setVenueIds($this->postIdMap);
            $this->FireflyFixtures->import();

            echo 'debugging line';

            $this->FireflyClubs->setVenueIds($this->postIdMap);
            $this->FireflyClubs->import();
        }

        protected function extractPost($oldPostId)
        {

            return $this->fetchPost($oldPostId);
        }

        protected function fetchPost($oldPostId)
        {

            $sql = "
                SELECT
                    `{$this->table}`.`{$this->titleField}` AS 'post_title',
                    'publish'                              AS 'post_status',
                    '{$this->type}'                        AS 'post_type',
                    'closed'                               AS 'comment_status'
                FROM
                  `{$this->table}`
                WHERE
                  `{$this->table}`.`{$this->slug}_id` = ?;
                  ";

            return $this->connection->fetchAssoc($sql, array($oldPostId));
        }

        /**
         * @param  string $oldPostId
         *
         * @return array  $oldPostMeta
         */
        protected function extractPostMeta($oldPostId)
        {

            $sql = "
            SELECT
            'events-calendar'  AS '_VenueOrigin',
            TRIM(TRAILING ', ' FROM CONCAT(address1, ', ', address2)) AS '_VenueAddress',
            `club`.`city`      AS '_VenueCity',
            'United Kingdom'   AS '_VenueCountry',
            `club`.`county`    AS '_VenueProvince',
            `club`.`post_code` AS '_VenueZip',
            1                  AS '_VenueShowMap',
            1                  AS '_VenueShowMapLink',
            `club`.`county`    AS '_VenueStateProvince'
            FROM
            `club`
            WHERE
            `club`.`club_id` = ?
        ";

            return $this->connection->fetchAssoc($sql, array($oldPostId));
        }

        /**
         * @param  string $oldPostId
         *
         * @return array  $oldPostMedia
         */
        protected function extractPostMedia($oldPostId)
        {

            return array();
        }

    }