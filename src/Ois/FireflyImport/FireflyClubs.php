<?php
    namespace Ois\FireflyImport;

    use Doctrine\DBAL\Connection;

    class FireflyClubs extends FireflyImport
    {
        /**
         * @var FireflyNews
         */
        protected $FireflyNews;

        /**
         * @var FireflyBoats
         */
        protected $FireflyBoats;

        protected $name       = 'Firefly Clubs';
        protected $type       = 'sailing-club';
        protected $table      = 'club';
        protected $slug       = 'club';
        protected $titleField = 'club';

        protected $postConditions = array();

        protected $venueIds = array();

        public function __construct(
            Connection $connection,
            FireflyNews $FireflyNews,
            FireflyBoats $FireflyBoats,
            $wpCli = 'wp',
            Array $wpCliArgs = array(),
            $encoding = 'UTF-8'
        )
        {

            parent::__construct($connection, $wpCli, $wpCliArgs, $encoding);

            $this->FireflyNews  = $FireflyNews;
            $this->FireflyBoats = $FireflyBoats;
        }

        public function import()
        {

            parent::import();

            $this->FireflyNews->setClubIds($this->postIdMap);
            $this->FireflyNews->import();

            $this->FireflyBoats->setClubIds($this->postIdMap);
            $this->FireflyBoats->import();
        }

        /**
         * @param  string $oldPostId
         * @return array  $oldPost
         */
        protected function extractPost($oldPostId) {

            $oldPost = $this->fetchPost($oldPostId);

            if(is_array($oldPost)) {

                $postContent = ($this->isGallery($oldPost)) ? "[gallery]\n" : '';

                $postContent .= (!empty($oldPost['post_content'])) ? $oldPost['post_content'] : '';

                $oldPost['post_content'] = $postContent;

            } else {

                $oldPost = array();
            }
            return $oldPost;
        }

        protected function fetchPost($oldPostId)
        {

            $sql = "
                SELECT
                    `club`.`pic_1`,
                    `club`.`pic_2`,
                    `club`.`pic_3`,
                    CONCAT('club_', `club`.`club_id`) AS 'post_name',
                    `club`.`club`                     AS 'post_title',
                    `club`.`club_info`                AS 'post_content',
                    'publish'                         AS 'post_status',
                    'sailing-club'                    AS 'post_type',
                    'closed'                          AS 'comment_status'
                FROM
                  `club`
                WHERE
                  `club`.`club_id` = ?;
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
                    `club`.`club_id`,
                    `club`.`directions`    AS 'wpcf-sailing-club-directions',
                    `club`.`contact_name`  AS 'wpcf-sailing-club-contact-name',
                    `club`.`contact_email` AS 'wpcf-sailing-club-contact-email',
                    `club`.`contact_tel`   AS 'wpcf-sailing-club-contact-phone',
                    `club`.`club_website`  AS 'wpcf-sailing-club-website'
                FROM
                    `club`
                WHERE
                    `club`.`club_id` = ?
                    ";

            $club = $this->connection->fetchAssoc($sql, array($oldPostId));

            $club['_wpcf_belongs_tribe_venue_id'] = (!empty($this->venueIds[$club['club_id']])) ? $this->venueIds[$club['club_id']] : NULL;
            unset($club['club_id']);

            return $club;
        }

        public function setVenueIds(Array $venueIds) {

            $this->venueIds = $venueIds;
        }
        protected function extractPostTerms($oldPostId)
        {
            $sql = "
                SELECT
                    `club`.`region`   AS 'region'
                FROM
                  `club`
                WHERE
                  `club`.`club_id` = ?
            ";

            return array_map(array($this, 'slug'), $this->connection->fetchAssoc($sql, array($oldPostId)));
        }
    }