<?php
namespace Ois\FireflyImport;

use \Doctrine\DBAL\Connection;
use Ois\WpCliImport\WpCliImport;

abstract class FireflyImport extends WpCliImport
{

    protected $connection;

    protected $name = 'Firefly';
    protected $type = 'post';
    protected $table;
    protected $slug;
    protected $titleField;

    protected $postConditions = array(
        'include' => 'Y'
    );

    protected $postIdMap = array();

    public function __construct(
        Connection $connection,
        $wpCli = 'wp',
        Array $wpCliArgs = array(),
        $encoding = 'UTF-8'
    )
    {
        parent::__construct($wpCli, $wpCliArgs, $encoding);

        $this->connection = $connection;
    }

    public function import() {

        echo "\nImporting {$this->name}...\n";

        $posts = $this->fetchPostIds($this->table, $this->slug, $this->titleField);

        foreach($posts as $post) {

            if (!empty($post['id'])) {

                $oldPostId = $post['id'];

                echo "\n - Importing {$this->type} ({$post['id']}): {$post['title']}\n";

                $this->postIdMap[$oldPostId] = $this->createPost($oldPostId);
            }
        }
    }

    public function getPostId($oldPostId) {

        if(empty($this->postIdMap)) {

            $this->import();
        }

        return (!empty($this->postIdMap[$oldPostId])) ? $this->postIdMap[$oldPostId] : NULL;
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

        $media = array();

        $oldPostMedia = $this->fetchMedia($oldPostId);

        if(!empty($oldPostMedia)) {

            $media[] = (!empty($oldPostMedia['pic_1'])) ? array(
                'file'    => $oldPostMedia['imageFile1'],
                'title'   => $oldPostMedia['title1'],
                'caption' => $oldPostMedia['title1'],
                'alt'     => $oldPostMedia['title1'],
                'desc'    => $oldPostMedia['description1']
            ) : NULL;

            $media[] = (!empty($oldPostMedia['pic_2'])) ? array(
                'file'    => $oldPostMedia['imageFile2'],
                'title'   => $oldPostMedia['title2'],
                'caption' => $oldPostMedia['title2'],
                'alt'     => $oldPostMedia['title2'],
                'desc'    => $oldPostMedia['description2']
            ) : NULL;

            $media[] = (!empty($oldPostMedia['pic_3'])) ? array(
                'file'    => $oldPostMedia['imageFile3'],
                'title'   => $oldPostMedia['title3'],
                'caption' => $oldPostMedia['title3'],
                'alt'     => $oldPostMedia['title3'],
                'desc'    => $oldPostMedia['description3']
            ) : NULL;

            $media = array_filter($media);
        };

        return $media;
    }

    protected function isGallery(Array $post) {

        return ($post['pic_1']) ? ($post['pic_2'] || $post['pic_3']) : ($post['pic_2'] && $post['pic_3']);

    }

    protected function getAuthorId($author) {

        $user_key = str_replace(' ', '', strtolower($author));

        $userId = $this->connection->fetchColumn('SELECT `user`.`user_id` FROM `user` WHERE `user`.`user_key` = ? ORDER BY `user`.`user_id` DESC', array($user_key));

        return ($userId && is_numeric($userId)) ? $userId : NULL;
    }

    protected function fetchPostIds($table, $slug, $titleField='title') {

        $conditions = array();

        foreach ($this->postConditions as $field => $value) {

            $conditions[] = "    `$table`.`$field` = '$value'\n";
        }

        $conditions = implode("AND\n", $conditions);

        $conditions = ($conditions) ? "WHERE\n$conditions" : '';

        $sql = $this->connection->prepare("
            SELECT
              `$table`.`{$slug}_id`  AS 'id',
              `$table`.`$titleField` AS 'title'
            FROM
              `$table`
            $conditions
            ORDER BY
              `$table`.`{$slug}_id` ASC;
              ");
        $sql->execute();

        return $sql->fetchAll();
    }

    /**
     * @param  int   $oldPostId
     *
     * @return array WordPress Post
     */
    abstract protected function fetchPost($oldPostId);


    /**
     * @param int    $oldPostId
     *
     * @return array mixed
     */
    protected function fetchMedia($oldPostId) {

        $sql = "
            SELECT
                `{$this->table}`.`pic_1`,
                `{$this->table}`.`pic_2`,
                `{$this->table}`.`pic_3`,
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
                    `{$this->table}`
            LEFT JOIN
              `media` `image1`
            ON
              `{$this->table}`.`pic_1` = `image1`.`media_id`
            LEFT JOIN
              `media` `image2`
            ON
              `{$this->table}`.`pic_2` = `image2`.`media_id`
            LEFT JOIN
              `media` `image3`
            ON
              `{$this->table}`.`pic_3` = `image3`.`media_id`
            WHERE
                `{$this->table}`.`{$this->slug}_id` = ?;
            ";
        return $this->connection->fetchAssoc($sql, array($oldPostId));
    }

    protected function indexById(Array $array, $field="ID") {

        $keys = array_map(function (Array $value) use ($field) {

            return (!empty($value[$field])) ? $value[$field] : NULL;

        }, $array);

        return array_combine($keys, $array);
    }
}