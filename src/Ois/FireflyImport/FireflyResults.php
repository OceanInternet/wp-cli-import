<?php
namespace Ois\FireflyImport;

class FireflyResults extends FireflyImport {

    protected $name       = 'Firefly Results';
    protected $type       = 'results';
    protected $table      = 'results';
    protected $slug       = 'result';
    protected $titleField = 'event_title';

    protected $boatIds  = array();
    protected $clubIds  = array();
    protected $venueIds = array();

    public function setClubIds(Array $clubIds) {

        $this->clubIds = $clubIds;
    }

    public function setBoatIds(Array $boatIds) {

        $this->boatIds = $boatIds;
    }

    public function setVenueIds(Array $venueIds) {

        $this->venueIds = $venueIds;
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPost
     */
    protected function extractPost($oldPostId) {

        return $this->fetchPost($oldPostId);
    }

    protected function fetchPost($oldPostId) {

        $sql = "
            SELECT
                CONCAT('result_id_', `results`.`result_id`) AS 'post_name',
                CONCAT(`results`.`event_title`, ' ', `results`.`year`) AS 'post_title',
                `results`.`year`                                       AS 'year',
                'publish'                                             AS 'post_status',
                '{$this->type}'                                       AS 'post_type',
                'closed'                                              AS 'comment_status'
            FROM
              `results`
            WHERE
              `results`.`result_id` = ?
              ";

        $result = $this->connection->fetchAssoc($sql, array($oldPostId));

        $result['post_date'] = $result['year'] . '01-01';
        unset($result['year']);

        return $result;
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
                    `results`.`year` AS 'wpcf-result-year',
                    `results`.`venue` AS 'wpcf-result-venue',
                    TRIM(CONCAT(
                        'F', `results`.`sail_no`, ' ',
                        `results`.`boat_name`)) AS 'wpcf-result-boat-name',
                    `results`.`helm` AS 'wpcf-result-helm',
                    `results`.`crew` AS 'wpcf-result-crew',
                    `results`.`club` AS 'wpcf-result-club',
                    `results`.`no_entries` AS 'wpcf-result-entries',
                    `results`.`club_id`,
                    `results`.`sail_no`,
                    `results`.`venue_id`
                FROM
                    `results`
                WHERE
                    `results`.`result_id` = ?
                    ";

        $result = $this->connection->fetchAssoc($sql, array($oldPostId));

        $result['_wpcf_belongs_sailing-club_id'] = (!empty($this->clubIds[$result['club_id']])) ? $result['club_id'] : NULL;
        unset($result['club_id']);

        $result['_wpcf_belongs_boat_id'] = (!empty($this->boatIds[$result['sail_no']])) ? $result['sail_no'] : NULL;
        unset($result['sail_no']);

        $result['_wpcf_belongs_tribe_venue_id'] = (!empty($this->venueIds[$result['venue_id']])) ? $result['venue_id'] : NULL;
        unset($result['venue_id']);

        return $result;
    }

    protected function extractPostTerms($oldPostId)
    {
        $sql = "
                SELECT
                    `results`.`event_type` AS 'tribe_events_cat'
                FROM
                  `results`
                WHERE
                  `results`.`event_id` = ?
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