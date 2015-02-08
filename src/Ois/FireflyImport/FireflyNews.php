<?php
namespace Ois\FireflyImport;

class FireflyNews extends FireflyImport {

    protected $name       = 'Firefly News';
    protected $type       = 'post';
    protected $table      = 'news';
    protected $slug       = 'article';
    protected $titleField = 'subject';

    protected $categories = array();

    protected $clubIds = array();

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
                `news`.`pic_1` AS 'pic_1',
                `news`.`pic_2` AS 'pic_2',
                `news`.`pic_3` AS 'pic_3',
                CONCAT('article_', `news`.`article_id`)     AS 'post_name',
                `news`.`subject`                            AS 'post_title',
                `news`.`article`                            AS 'post_content',
                `news`.`intro`                              AS 'post_excerpt',
                CONCAT(`news`.`date`,' ',`news`.`time`)     AS 'post_date',
                'publish'                                   AS 'post_status',
                'post'                                      AS 'post_type',
                'closed'                                    AS 'comment_status',
                `news`.`author`                             AS 'author',
                `news`.`type`                               AS 'category'
            FROM
              `news`
            WHERE
              `news`.`article_id` =  ?
              ";

        $article = $this->connection->fetchAssoc($sql, array($oldPostId));

        $article['post_author']   = $this->getAuthorId($article['author']);

        return $article;
    }

    protected function getCategoryId($name) {

        $name = ucwords($name);
        $slug = $this->slug($name);

        if(empty($this->categories[$slug])) {

            $categoryId = $this->wpCli(
                array('term', 'create', 'category', escapeshellarg($name)),
                array('slug' => $slug, 'porcelain')
            );

            $this->isSaved($categoryId, 'Category');

            $this->categories[$slug] = ($categoryId && is_numeric($categoryId)) ? $categoryId : NULL;
        }

        return $this->categories[$slug];
    }

    protected function setPostContent(&$post) {

        $content = '';

        $content .= ($post['post_excerpt'])   ? "<p class=\"excerpt\">{$post['post_excerpt']}</p>\n" : '';
        $content .= ($this->isGallery($post)) ? "[gallery]\n"                                           : '';

        $content .= $post['post_content'];

        $post['post_content']  = $content;
    }

    protected function extractPostMeta($oldPostId)
    {
        $sql = "
            SELECT
                `news`.`club_id`,
                `news`.`type` AS 'post_tag'
            FROM
                `news`
            WHERE
                `news`.`article_id` = ?
                ";

        $news = $this->connection->fetchAssoc($sql, array($oldPostId));

        $news['_wpcf_belongs_sailing-club_id'] = (!empty($this->clubIds[$news['club_id']])) ? $this->clubIds[$news['club_id']] : NULL;
        unset($news['club_id']);

        return $news;
    }

    public function setClubIds(Array $clubIds) {

        $this->clubIds = $clubIds;
    }

}