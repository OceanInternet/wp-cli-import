<?php
namespace Ois\FireflyImport;

class FireflyResults extends FireflyImport {

    protected $name       = 'Firefly Results';
    protected $type       = 'result';
    protected $table      = 'results';
    protected $slug       = 'result';
    protected $titleField = 'event_title';

    protected $postConditions = array();

    protected $boatIds  = array();
    protected $clubIds  = array();

    protected function fetchPostIds($table, $slug, $titleField='title') {

        $this->setBoatIds();
        $this->setClubIds();

        return parent::fetchPostIds($table, $slug, $titleField);
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
                CONCAT('result_id_', `results`.`result_id`)            AS 'post_name',
                CONCAT(`results`.`event_title`, ' ', `results`.`year`) AS 'post_title',
                CONCAT(`results`.`year`, '-01-01')                     AS 'post_date',
                'publish'                                              AS 'post_status',
                '{$this->type}'                                        AS 'post_type',
                'closed'                                               AS 'comment_status'
            FROM
              `results`
            WHERE
              `results`.`result_id` = ?
              ";

        $result = $this->connection->fetchAssoc($sql, array($oldPostId));

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
                    `results`.`year`  AS 'wpcf-result-year',
                    `results`.`venue` AS 'wpcf-result-venue',
                    TRIM(CONCAT(
                        'F', `results`.`sail_no`, ' ',
                        `results`.`boat_name`)) AS 'wpcf-result-boat-name',
                    `results`.`helm`            AS 'wpcf-result-helm',
                    `results`.`crew`            AS 'wpcf-result-crew',
                    `results`.`club`            AS 'wpcf-result-club',
                    `results`.`no_entries`      AS 'wpcf-result-entries',
                    `results`.`club_id`,
                    `results`.`sail_no`,
                    `results`.`venue_id`
                FROM
                    `results`
                WHERE
                    `results`.`result_id` = ?
                    ";

        $result = $this->connection->fetchAssoc($sql, array($oldPostId));

        $result['_wpcf_belongs_sailing-club_id'] = (!empty($this->clubIds["club_{$result['club_id']}"]))  ? $this->clubIds["club_{$result['club_id']}"]['ID'] : NULL;
        unset($result['club_id']);

        $result['_wpcf_belongs_tribe_venue_id']  = (!empty($this->clubIds["club_{$result['venue_id']}"])) ? $this->clubIds["club_{$result['club_id']}"]['_wpcf_belongs_tribe_venue_id'] : NULL;
        unset($result['venue_id']);

        $result['_wpcf_belongs_boat_id'] = (!empty($this->boatIds["sail_no_{$result['sail_no']}"]['ID'])) ? $this->boatIds["sail_no_{$result['sail_no']}"]['ID'] : NULL;
        unset($result['sail_no']);

        return $result;
    }

    protected function extractPostTerms($oldPostId)
    {
        $sql = "
                SELECT
                    `results`.`event_type` AS 'post_tag'
                FROM
                  `results`
                WHERE
                  `results`.`result_id` = ?
            ";

        return array_map(array($this, 'slug'), $this->connection->fetchAssoc($sql, array($oldPostId)));
    }

    protected function setBoatIds() {

        echo " -- Setting Boat ID's\n";

        $json = $this->wpCli(
            array('post', 'list'),
            array(
                'fields'    => 'ID,post_name',
                'post_type' => 'boat',
                'format'    => 'json'
            )
        );

        $boatIds = json_decode($json, TRUE);

        $this->boatIds = $this->indexById($boatIds, 'post_name');
    }

    protected function setClubIds() {

        echo " -- Setting Club/Venue ID's\n";

        $json = $this->wpCli(
            array('post', 'list'),
            array(
                'fields'    => 'ID,post_name',
                'post_type' => 'sailing-club',
                'format'    => 'json'
            )
        );

        $clubIds = json_decode($json, TRUE);

        $clubIds = array_map(function ($value) {

            $value['_wpcf_belongs_tribe_venue_id'] = $this->wpCli(array('post','meta', 'get', $value['ID'], ' _wpcf_belongs_tribe_venue_id'));

            return $value;

        }, $clubIds);

        print_r($clubIds);

        $this->clubIds = $this->indexById($clubIds, 'post_name');
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