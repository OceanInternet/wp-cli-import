<?php
    namespace Ois\FireflyImport;

    use Doctrine\DBAL\Connection;

    class FireflyBoats extends FireflyImport
    {
        /**
         * @var FireflyResults
         */
        protected $FireflyResults;

        protected $name       = 'Firefly Boats';
        protected $type       = 'boat';
        protected $table      = 'boats';
        protected $slug       = 'sail_no';
        protected $titleField = 'sail_no';

        protected $postConditions = array();

        protected $clubIds = array();

        public function __construct(
            Connection $connection,
//            FireflyResults $FireflyResults,
            $wpCli = 'wp',
            Array $wpCliArgs = array(),
            $encoding = 'UTF-8'
        )
        {

            parent::__construct($connection, $wpCli, $wpCliArgs, $encoding);

//            $this->FireflyResults  = $FireflyResults;
        }

        public function import()
        {

            parent::import();

//            $this->FireflyResults->setBoatIds($this->postIdMap);
//            $this->FireflyResults->import();
        }

        /**
         * @param  string $oldPostId
         * @return array  $oldPost
         */
        protected function extractPost($oldPostId) {

            return $this->fetchPost($oldPostId);
        }

        protected function fetchPost($oldPostId)
        {

            $sql = "
                SELECT
                    CONCAT('sail_no_', `boats`.`sail_no`) AS 'post_name',
                    CONCAT('F', `boats`.`sail_no`)        AS 'post_title',
                    `boats`.`additional_notes`            AS 'post_content',
                    'publish'                             AS 'post_status',
                    'boat'                                AS 'post_type',
                    `boats`.`owner_id`                    AS 'post_author',
                    'closed'                              AS 'comment_status'
                FROM
                  `boats`
                WHERE
                  `boats`.`sail_no` = ?
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
                    `boats`.`boat_name`     AS 'wpcf-boat-name',
                    `boats`.`builder`       AS 'wpcf-boat-builder',
                    `boats`.`deck_colour`   AS 'wpcf-boat-deck-colour',
                    `boats`.`hull_colour`   AS 'wpcf-boat-hull-colour',
                    `boats`.`usage`         AS 'wpcf-boat-usage',
                    `boats`.`condition`     AS 'wpcf-boat-condition',
                    `boats`.`location`      AS 'wpcf-boat-location',
                    `boats`.`known_owners`  AS 'wpcf-boat-known-owners',
                    `boats`.`history`       AS 'wpcf-boat-history',
                    `boats`.`current_owner` AS 'wpcf-boat-current-owner',
                    `boats`.`club_id`
                FROM
                    `boats`
                WHERE
                    `boats`.`sail_no` = ?
                    ";

            $boat = $this->connection->fetchAssoc($sql, array($oldPostId));

            $boat['_wpcf_belongs_sailing-club_id'] = (!empty($this->clubIds[$boat['club_id']])) ? $this->clubIds[$boat['club_id']] : NULL;
            unset($boat['club_id']);

            return $boat;
        }

        protected function setVenueIds(Array $venueIds) {

            $this->venueIds = $venueIds;
        }
        protected function extractPostTerms($oldPostId)
        {
            $sql = "
                SELECT
                    `boats`.`region` AS 'region'
                FROM
                  `boats`
                WHERE
                  `boats`.`sail_no` = ?
            ";

            return array_map(array($this, 'slug'), $this->connection->fetchAssoc($sql, array($oldPostId)));
        }

        /**
         * @param  string $oldPostId
         * @return array  $oldPostMedia
         */
        protected function extractPostMedia($oldPostId) {

            $sql = "
                SELECT
                    CONCAT(
                        'http://fireflysailing.org.uk/images/media/Photo/register/',
                        `boats`.`picture`) AS 'file',
                    TRIM(CONCAT(
                        'F', `boats`.`sail_no`, ' ',
                        `boats`.`boat_name`)) AS 'title',
                    TRIM(CONCAT(
                        'F', `boats`.`sail_no`, ' ',
                        `boats`.`boat_name`)) AS 'caption',
                    TRIM(CONCAT(
                        'F', `boats`.`sail_no`, ' ',
                        `boats`.`boat_name`)) AS 'alt',
                    TRIM(CONCAT(
                        'F', `boats`.`sail_no`, ' ',
                        `boats`.`boat_name`)) AS 'desc'
                FROM
                    `boats`
                WHERE
                    `boats`.`sail_no` = ?
                AND
                    `boats`.`boat_name` IS NOT NULL;
            ";

            $picture = $this->connection->fetchAssoc($sql, array($oldPostId));

            return (!empty($picture)) ? array($picture) : NULL;
        }

        public function setClubIds(Array $clubIds) {

            $this->clubIds = $clubIds;
        }
    }