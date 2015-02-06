<?php
namespace Ois\FireflyImport;

class FireflyVenues extends FireflyImport {

    protected $name       = 'Firefly Clubs (venues)';
    protected $type       = 'tribe_venue';
    protected $table      = 'club';
    protected $slug       = 'club';
    protected $titleField = 'club';

    protected $postConditions = array();


    protected function extractPost($oldPostId) {

        return $this->fetchPost($oldPostId);
    }

    protected function fetchPost($oldPostId) {

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
     * @return array  $oldPostMeta
     */
    protected function extractPostMeta($oldPostId) {

        $sql =
            "
            SELECT
            'events-calendar'  AS '_VenueOrigin',
            TRIM(TRAILING ', ' FROM CONCAT(address1, ', ', address2)) AS '_VenueAddress',
            `club`.`city`      AS '_VenueCity',
            `club`.`country`   AS '_VenueCountry',
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


}