<?php
namespace Ois\WpCliImport;

abstract class WpCliImport
{

    protected $wpCli;
    protected $wpCliArgs;
    protected $encoding;

    protected $post = array(
        'post_content'   => NULL, // The full text of the post.
        'post_name'      => NULL, // The name (slug) for your post
        'post_title'     => NULL, // The title of your post.
        'post_status'    => NULL, // Default 'draft'.
        'post_type'      => NULL, // Default 'post'.
        'post_author'    => NULL, // The user ID number of the author. Default is the current user ID.
        'post_parent'    => NULL, // Sets the parent of the new post, if any. Default 0.
        'menu_order'     => NULL, // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
        'post_excerpt'   => NULL, // For all your post excerpt needs.
        'post_date'      => NULL, // The time post was made.
        'comment_status' => NULL, // Default is the option 'default_comment_status', or 'closed'.
        'post_category'  => NULL, // Default empty.
        'tags_input'     => NULL  // Default empty.
    );

    protected $media = array(
        'file'    => NULL,
        'title'   => NULL,
        'caption' => NULL,
        'alt'     => NULL,
        'desc'    => NULL
    );

    /**
     * @param string $wpCli
     * @param array  $wpCliArgs
     * @param string $encoding
     */
    public function __construct(
        $wpCli='wp',
        Array $wpCliArgs=array(),
        $encoding='UTF-8'
    ) {

        $this->wpCli     = $wpCli;
        $this->wpCliArgs = $wpCliArgs;
        $this->encoding  = $encoding;
    }

    /**
     * Import all the things...
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    abstract public function import();

    /**
     * @param $oldPostId
     * @return bool|int|string
     */
    protected function createPost($oldPostId)
    {
        $post = $this->extractPost($oldPostId);

        if(!is_array($post)) {
            return;
        }

        $this->clean('post', $post);
        $post[] = 'porcelain';

        $postId = $this->wpCli(array('post', 'create'), $post);

        if($this->isSaved($postId, 'Post')) {

            $this->setPostMedia($postId, $this->extractPostMedia($oldPostId));
            $this->setPostMeta($postId,  $this->extractPostMeta($oldPostId));
        }
    }

    /**
     * @param  array $command
     * @param  array $args
     *
     * @return bool|string
     */
    protected function wpCli(Array $command, Array $args=array()) {

        $result = FALSE;

        $wpCli   = $this->wpCli;
        $command = implode(' ', $command);
        $args    = $this->getWpCliArgs($args);

        $command = "$wpCli $command $args";

        exec($command, $result);

        return (is_array($result)) ? implode(PHP_EOL, $result) : $result;
    }

    protected function getWpCliArgs(Array $args) {

        $argArray = array();

        foreach($args as $k => $v) {

            if(is_numeric($k)) {

                $k = $v;
                $v = NULL;
            }

            if($v) {

                $v = escapeshellarg($v);

                $argArray[] = "--$k=$v";

            } else {

                $argArray[] = "--$k";
            }
        }

        return implode(' ', $argArray);
    }

    protected function clean($type, Array &$post) {

        // Remove invalid fields
        $post = array_intersect_key($post, $this->$type);

        // Remove empty fields
        $post = array_filter($post, function ($value) { return !empty($value); });

        // Convert to encoding to $this->encoding
        $post = array_map(array($this, 'encode'), $post);
    }

    protected function encode($value) {

        return mb_convert_encoding($value, $this->encoding);
    }

    protected function isSaved($id, $type) {

        if(!$id || !is_numeric($id)) {

            echo " -- Could not save $type: $id\n";
            return FALSE;

        } else {

            echo " -- Created $type: $id\n";
            return TRUE;
        }
    }

    /**
     * @param int     $postId
     * @param array   $postMedia
     * @param boolean $setFeatured
     */
    protected function setPostMedia($postId, Array $postMedia, $setFeatured=TRUE)
    {
        foreach($postMedia as $media) {

            $file = escapeshellarg(str_replace(' ', '%20', $media['file']));

            unset($media['file']);

            $this->clean('media', $media);

            $media['post_id'] = $postId;

            if($setFeatured) {

                $media[] = 'featured_image';
                $setFeatured = FALSE;
            }

            $media[] = 'porcelain';

            $mediaId = $this->wpCli(array('media', 'import', $file), $media);

            $this->isSaved($mediaId, 'Media');
        }
    }

    /**
     * @param int   $postId
     * @param array $postMeta
     */
    protected function setPostMeta($postId, Array $postMeta) {

        foreach($postMeta as $key => $value) {

            $key   = escapeshellarg($key);
            $value = escapeshellarg($value);

            echo ' -- ' . $this->wpCli(array('post', 'meta', 'add', $postId, $key, $value)) . "\n";
        }
    }

    protected function setPostTerms($postId, Array $postTerms) {

        foreach($postTerms as $taxonomy => $term) {

            $taxonomy = escapeshellarg($taxonomy);
            $term     = escapeshellarg($term);

            echo ' -- ' . $this->wpCli(array('post', 'term', 'add', $postId, $taxonomy, $term)) . "\n";
        }
    }

    /**
     * @param  string $oldPostId
     * @return array  $oldPost
     */
    abstract protected function extractPost($oldPostId);

    /**
     * @param  string $oldPostId
     * @return array  $oldPostMeta
     */
    abstract protected function extractPostMeta($oldPostId);

    /**
     * @param  string $oldPostId
     * @return array  $oldPostTerms
     */
    abstract protected function extractPostTerms($oldPostId);

    /**
     * @param  string $oldPostId
     * @return array  $oldPostMedia
     */
    abstract protected function extractPostMedia($oldPostId);
}