<?php
namespace Ois\WpCliImport;

use \Doctrine\DBAL\Connection;

class FireflyPageImporter extends WpCliImport {

    protected $connection;

    public function __construct(
        Connection $connection,
        $wpCli='wp',
        Array $wpCliArgs=array(),
        $encoding='UTF-8'
    )
    {
        parent::__construct($wpCli, $wpCliArgs, $encoding);

        $this->connection = $connection;
    }

    public function import() {

        echo "Importing Firefly Pages...\n";

        $sql = $this->connection->prepare("
SELECT
  `editorial`.`editorial_id`
FROM
  `editorial`
WHERE
  `editorial`.`include` =  'Y'
ORDER BY
  `editorial`.`date`         ASC,
  `editorial`.`editorial_id` ASC;
  ");
        $sql->execute();

        $results = $sql->fetchAll();

        foreach($results as $editorial) {

            if (!empty($editorial['editorial_id'])) {

                echo "Importing Page: {$editorial['editorial_id']}\n";

                $this->createPost($editorial['editorial_id']);
            }
        }
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPost
     */
    protected function extractPost($oldPostId) {

        $sql = "
SELECT
    `editorial`.`pic_1`,
    `editorial`.`pic_2`,
    `editorial`.`pic_3`,
    CONCAT('editorial_', `editorial`.`editorial_id`) AS 'post_name',
    `editorial`.`title`                              AS 'post_title',
    `editorial`.`text`                               AS 'post_content',
    `editorial`.`date`                               AS 'post_date',
    'publish'                                        AS 'post_status',
    'page'                                           AS 'post_type',
    'closed'                                         AS 'comment_status'
FROM
  `editorial`
WHERE
  `editorial`.`editorial_id` = ?;
              ";

        $oldPost = $this->connection->fetchAssoc($sql, array($oldPostId));

        if(is_array($oldPost)) {

            $isGallery = ($oldPost['pic_1']) ? ($oldPost['pic_2'] || $oldPost['pic_3']) : ($oldPost['pic_2'] && $oldPost['pic_3']);

            $postContent = ($isGallery) ? "[gallery]\n" : '';

            $postContent .= (!empty($oldPost['post_content'])) ? $oldPost['post_content'] : '';

            $oldPost['post_content'] = $postContent;

        } else {

            $oldPost = array();
        }
        return $oldPost;
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPostMeta
     */
    protected function extractPostMeta($oldPostId) {

        $oldPostMeta = array();

        return $oldPostMeta;
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPostTerms
     */
    protected function extractPostTerms($oldPostId) {

        $oldPostTerms = array();

        return $oldPostTerms;
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPostMedia
     */
    protected function extractPostMedia($oldPostId) {

        $oldPostMedia = array();

        $sql = "
SELECT
    `editorial`.`pic_1`, `editorial`.`pic_1_title`,
    `editorial`.`pic_2`, `editorial`.`pic_2_title`,
    `editorial`.`pic_3`, `editorial`.`pic_3_title`,
    `image1`.`title` AS `title1`, `image1`.`description` AS `description1`,
    `image2`.`title` AS `title2`, `image2`.`description` AS `description2`,
    `image3`.`title` AS `title3`, `image3`.`description` AS `description3`,
    CONCAT(
        'http://fireflysailing.org.uk/images/media/',
        `image1`.`directory`,
        '/',
        `image1`.`filename`
    ) AS `imageFile1`,
    CONCAT(
        'http://fireflysailing.org.uk/images/media/',
        `image2`.`directory`,
        '/',
        `image2`.`filename`
    ) AS `imageFile2`,
    CONCAT(
        'http://fireflysailing.org.uk/images/media/',
        `image3`.`directory`,
        '/',
        `image3`.`filename`
    ) AS `imageFile3`
FROM
        `editorial`
LEFT JOIN
  `media` `image1`
ON
  `editorial`.`pic_1` = `image1`.`media_id`
LEFT JOIN
  `media` `image2`
ON
  `editorial`.`pic_2` = `image2`.`media_id`
LEFT JOIN
  `media` `image3`
ON
  `editorial`.`pic_3` = `image3`.`media_id`
WHERE
    `editorial`.`editorial_id` = ?;
";
        $results = $this->connection->fetchAssoc($sql, array($oldPostId));

        if(!empty($results)) {

            $oldPostMedia[] = (!empty($results['pic_1'])) ? array(
                'file'    => $results['imageFile1'],
                'title'   => $results['title1'],
                'caption' => $results['title1'],
                'alt'     => $results['title1'],
                'desc'    => $results['description1']
            ) : NULL;

            $oldPostMedia[] = (!empty($results['pic_2'])) ? array(
                'file'    => $results['imageFile2'],
                'title'   => $results['title2'],
                'caption' => $results['title2'],
                'alt'     => $results['title2'],
                'desc'    => $results['description2']
            ) : NULL;

            $oldPostMedia[] = (!empty($results['pic_3'])) ? array(
                'file'    => $results['imageFile3'],
                'title'   => $results['title3'],
                'caption' => $results['title3'],
                'alt'     => $results['title3'],
                'desc'    => $results['description3']
            ) : NULL;

            $oldPostMedia = array_filter($oldPostMedia);
        };

        return $oldPostMedia;
    }
}