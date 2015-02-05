<?php
namespace Ois\FireflyImport;

class FireflyRegions extends FireflyImport {

    protected $name       = 'Firefly Regions';
    protected $type       = 'term';
    protected $taxonomy   = 'region';
    protected $table      = 'region';
    protected $slug       = 'region';
    protected $titleField = 'region';

    public function import() {

        echo "\nImporting {$this->name}...\n";

        $posts = $this->fetchPostIds($this->table, $this->slug, $this->titleField);

        foreach($posts as $post) {

            if (!empty($post['id'])) {

                echo "\n - Importing {$this->type}: {$post['title']}\n";

                $this->createPost($post['title']);
            }
        }
    }

    protected function createPost($region) {

        $region = ucwords($region);
        $slug = $this->slug($region);

        $regionId = $this->wpCli(
            array('term', 'create', 'region', escapeshellarg($region)),
            array('slug' => $slug, 'porcelain')
        );

        $this->isSaved($regionId, 'Region');
    }

    protected function fetchPost($oldPostId) {

        return array();
    }

    protected function extractPost($oldPostId) {

        return array();
    }
}