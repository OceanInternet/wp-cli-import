<?php
namespace Ois\FireflyImport;

class FireflyUsers extends FireflyImport
{

    protected $name = 'Firefly Users';
    protected $type = 'user';

    public function import()
    {

        echo "\nImporting {$this->name}...\n";

        $posts = $this->fetchUserIds();

        foreach ($posts as $post) {

            if (!empty($post['id'])) {

                echo "\n - Importing {$this->type}: {$post['title']}\n";

                $this->createUser($post['id']);
            }
        }
    }

    protected function fetchUserIds()
    {

        $sql = $this->connection->prepare("
            SELECT
                `user`.`user_id`  AS 'id',
                `user`.`username` AS 'title'
            FROM
                `user`
            ORDER BY
                `user`.`user_id` DESC
        ");

        $sql->execute();

        return $sql->fetchAll();
    }

    protected function fetchUser($userId)
    {

        $sql = "
            SELECT
                'subscriber'             AS 'role',
                `user`.`username`        AS 'user_login',
                `user`.`email`           AS 'user_email',
                `user`.`registered_date` AS 'user_registered',
                `user`.`firstname`       AS 'first_name',
                `user`.`surname`         AS 'last_name'
            FROM
                `user`
            WHERE
                `user`.`user_id` = ?;
        ";

        return $this->connection->fetchAssoc($sql, array($userId));
    }

    protected function createUser($userId)
    {

        $user = $this->fetchUser($userId);

        $user['user_email'] = (!empty($user['user_email'])) ? $user['user_email'] : "{$user['user_login']}.user@fireflyclass.co.uk";

        $user['display_name'] = (!empty($user['first_name']) || !empty($user['last_name'])) ? trim("{$user['first_name']} {$user['last_name']}") : NULL;

        echo ' -- ' . $this->wpCli(array('user', 'update', $userId), $user);
    }

    protected function createPost($region)
    {

    }

    protected function fetchPost($oldPostId)
    {

        return array();
    }

    protected function extractPost($oldPostId)
    {

        return array();
    }
}