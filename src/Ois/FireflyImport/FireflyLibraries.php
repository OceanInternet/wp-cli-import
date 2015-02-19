<?php
namespace Ois\FireflyImport;

class FireflyLibraries extends FireflyImport {

    protected $name       = 'Firefly Libraries';
    protected $type       = 'post';
    protected $table      = 'library';
    protected $slug       = 'library';
    protected $titleField = 'title';

    protected $categoryId = NULL;

    /**
     * @param  string $oldPostId
     * @return array  $oldPost
     */
    protected function extractPost($oldPostId) {

        $oldPost = $this->fetchPost($oldPostId);

        if(is_array($oldPost)) {

            $this->setPostContent($oldPost);

        } else {

            $oldPost = array();
        }
        return $oldPost;
    }

    protected function fetchPost($oldPostId) {

        $sql = "
            SELECT
                CONCAT('library', `library`.`library_id`) AS 'post_name',
                CONCAT(`library`.`year`, '-01-01')        AS 'post_date',
                `library`.`title`                         AS 'post_title',
                `library`.`description`                   AS 'post_content',
                'publish'                                 AS 'post_status',
                'post'                                    AS 'post_type',
                'closed'                                  AS 'comment_status'
            FROM
              `library`
            WHERE
              `library`.`library_id` =  ?
              ";

        $library = $this->connection->fetchAssoc($sql, array($oldPostId));
        $library['post_category'] = $this->getCategoryId();

        return $library;
    }

    protected function getCategoryId() {

        if(!$this->categoryId) {

            $name = 'Gallery';
            $slug = $this->slug($name);

            $categoryId = $this->wpCli(
                array('term', 'create', 'category', escapeshellarg($name)),
                array('slug' => $slug, 'porcelain')
            );

            $this->isSaved($categoryId, 'Category');

            $this->categoryId = ($categoryId && is_numeric($categoryId)) ? $categoryId : NULL;
        }

        return $this->categoryId;
    }

    protected function setPostContent(&$post) {

        $post['post_content']  = "{$post['post_content']}\n[gallery]\n";
    }

    protected function extractPostMedia($oldPostId) {

        $sql = $this->connection->prepare("
            SELECT
                CONCAT(
                    'http://fireflysailing.org.uk/images/media/',
                    `media`.`directory`,
                    '/',
                    `media`.`filename`
                )                     AS 'file',
                `media`.`title`       AS title,
                `media`.`title`       AS caption,
                `media`.`title`       AS alt,
                `media`.`description` AS 'desc'
            FROM
              `media`
            WHERE
              `media`.`library_id` = ?
            AND
              `media`.`directory` <> ''
            AND
              `media`.`filename` <> ''
            AND
              `media`.`include` = 'Y'
            ORDER BY
              `media`.`media_id` ASC
            ");
        $sql->execute(array($oldPostId));

        return $sql->fetchAll();
    }
}