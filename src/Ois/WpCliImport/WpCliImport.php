<?php
namespace Ois\WpCliImport;

abstract class WpCliImport
{

    protected $wpCli = 'php wp-cli.phar';

    protected $post = array(
        'ID'                    => NULL,
        'post_content'          => NULL,
        'post_name'             => NULL,
        'post_title'            => NULL,
        'post_status'           => NULL,
        'post_type'             => NULL,
        'post_author'           => NULL,
        'ping_status'           => NULL,
        'post_parent'           => NULL,
        'menu_order'            => NULL,
        'to_ping'               => NULL,
        'pinged'                => NULL,
        'post_password'         => NULL,
        'guid'                  => NULL,
        'post_content_filtered' => NULL,
        'post_excerpt'          => NULL,
        'post_date'             => NULL,
        'post_date_gmt'         => NULL,
        'comment_status'        => NULL,
        'post_category'         => NULL,
        'tags_input'            => NULL,
        'tax_input'             => NULL,
        'page_template'         => NULL
    );

    function setMeta($postId, Array $post)
    {

        $type = $post['post_type'];

        foreach ($post as $field => $value) {

            if (strpos($field, "wpcf-$type-") === 0) {

                $name = str_replace('-', ' ', str_replace("wpcf-$type-", '', $field));

                echo "Setting: $name -> " . print_r($value) . "\n";

                $field = escapeshellarg($field);
                $value = escapeshellarg($value);

                exex("{$this->wpCli} post meta set $postId $field $value");
            }
        }
    }

    function createPost(Array $oldPost)
    {
        $createCommand = "{$this->wpCli} post create --porcelain";

        $this->setPostContent($oldPost);

        $post = array_intersect_key($oldPost, $this->post);

        $post['post_content'] = mb_convert_encoding($post['post_content'], 'UTF-8');

        $post = array_map(function ($value) {

            return ($value == 'NULL') ? NULL : (!empty($value)) ? escapeshellarg($value) : NULL;
        }, $post);

        $post = array_filter($post, function ($v) {
            return !empty($v);
        });

        foreach ($post as $k => $v) {
            $createCommand .= " --$k=$v";
        }

        $postId = $createCommand;

        return (!empty($postId) && is_numeric($postId)) ? $postId : FALSE;
    }

    abstract protected function setPostContent(Array &$oldPost);

    abstract protected function extractImages(Array $oldPost);
}
