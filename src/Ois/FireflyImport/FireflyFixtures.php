<?php
    namespace Ois\FireflyImport;

    class FireflyFixtures extends FireflyImport
    {

        protected $name       = 'Firefly Fixtures';
        protected $type       = 'tribe_events';
        protected $table      = 'fixtures';
        protected $slug       = 'fixture';
        protected $titleField = 'event_title';

        protected $postConditions = array('show_event' => 'Y');

        protected $clubIds = array();

        protected function extractPost($oldPostId)
        {

            return $this->fetchPost($oldPostId);
        }

        protected function fetchPost($oldPostId)
        {

            $sql = "
                SELECT
                    `fixtures`.`event_title` AS 'post_title',
                    `fixtures`.`details`     AS 'post_content',
                    `fixtures`.`start_date`  AS 'post_date',
                    'publish'                AS 'post_status',
                    'post'                   AS 'post_type',
                    'closed'                 AS 'comment_status'
                FROM
                    `fixtures`
                WHERE
                    `fixtures`.`fixture_id` = ?
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
                    'events-calendar'        AS '_EventOrigin',
                    1                        AS '_EventShowMapLink',
                    1                        AS '_EventShowMap',
                    'yes'                    AS '_EventAllDay',
                    `fixtures` . `start_date` AS '_EventStartDate',
                    `fixtures` . `end_date`   AS '_EventEndDate',
                    '£'                      AS '_EventCurrencySymbol',
                    'prefix'                 AS '_EventCurrencyPosition',
                    NULL                     AS '_EventOrganizerID',
                    `fixtures` . `club_id`    AS 'club_id'
                FROM
                    `fixtures`
                WHERE
                    `fixtures`.`fixture_id` = ?
            ";

            $event = $this->connection->fetchAssoc($sql, array($oldPostId));

            $event['_EventVenueID'] = (!empty($this->clubIds[$event['club_id']])) ? $this->clubIds[$event['club_id']] : NULL;
            unset($event['club_id']);

            return $event;
        }

        public function setClubIds(Array $venueIds)
        {

            $this->clubIds = $venueIds;
        }

        protected function extractPostTerms($oldPostId)
        {
            $sql = "
                SELECT
                    `fixtures`.`region`   AS 'region',
                    `fixtures`.`event`    AS 'post_tag',
                    `fixtures`.`category` AS 'tribe_events_cat'
                FROM
                  `fixtures`
                WHERE
                  `fixtures`.`fixture_id` = ?
            ";

            return array_map(array($this, 'slug'), $this->connection->fetchAssoc($sql, array($oldPostId)));
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