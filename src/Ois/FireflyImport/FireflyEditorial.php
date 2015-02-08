<?php
namespace Ois\FireflyImport;

class FireflyEditorial extends FireflyImport {

    protected $name       = 'Firefly Editorial';
    protected $type       = 'page';
    protected $table      = 'editorial';
    protected $slug       = 'editorial';
    protected $titleField = 'title';

    protected $postConditions = array();

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

    protected function fetchPost($oldPostId) {

        $sql = "
SELECT
    `editorial`.`pic_1` AS 'pic_1',
    `editorial`.`pic_2` AS 'pic_2',
    `editorial`.`pic_3` AS 'pic_3',
    `editorial`.`include`,
    CONCAT('editorial_', `editorial`.`editorial_id`) AS 'post_name',
    `editorial`.`title`                              AS 'post_title',
    `editorial`.`text`                               AS 'post_content',
    `editorial`.`date`                               AS 'post_date',
    '{$this->type}'                                  AS 'post_type',
    'closed'                                         AS 'comment_status'
FROM
  `editorial`
WHERE
  `editorial`.`editorial_id` = ?;
              ";

        $post = $this->connection->fetchAssoc($sql, array($oldPostId));

        $post['post_status'] = ($post['include'] == 'Y') ? 'publish' : 'draft';

        return $post;
    }
}