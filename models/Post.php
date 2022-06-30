<?php

class Post {
    // properties
    public $post_id;
    public $post_title;
    public $post_body;
    public $post_img;
    public $post_user_id;
    public $conn;
    public $post = [];
    public $posts = [];
    public $errors = [];
    public $offset;
    public $limit;
    public $num_posts;

    // constructor (inject DB conn)
    public function __construct($conn) {
        $this->conn = $conn;
        $this->countNumPosts();
    }
    // Post methods
    // count the number of posts so pager can be calc'd
    public function countNumPosts() {
        $sql = "SELECT COUNT(id) AS num_posts FROM posts";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch_assoc();
        $this->num_posts = $result['num_posts'];
    }
    // "setter" for the post prop
    public function fetchPost($id) {
        $this->post_id = $id;
        $sql = "SELECT posts.*, username
                FROM posts
                JOIN users ON users.id = posts.user_id
                WHERE posts.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows !== 1) {
            $this->errors['fetch_err'] = "Couldn't retrieve resource!";
        } else {
            $this->post = $result->fetch_assoc();
        }
        return $this;
    }

    public function calcPager() {
        $this->num_pages = ceil($this->num_posts / $this->limit);
    }

    public function getNumPages() {
        return $this->num_pages;
    }

    public function fetchPosts($offset, $limit) {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->calcPager();
        $sql = "SELECT posts.*, users.username, COUNT(comments.id) AS num_comments
                FROM posts 
                JOIN users ON users.id = posts.user_id
                LEFT JOIN comments ON posts.id = comments.post_id
                GROUP BY posts.id
                LIMIT ?,?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $results = $stmt->get_result();
        if($results->num_rows === 0) {
            $this->errors['fetch_err'] = "Couldn't retrieve resource!";
        } else {
            $this->posts = $results->fetch_all(MYSQLI_ASSOC);
        }
        return $this;
    }

    public function getPost() {
        return $this->post;
    }

    public function getPosts() {
        return $this->posts;
    }

    public function validatePost($post, $file) {
        $this->post_title = strip_tags($post['title'], ['br','p', 'h1', 'h2']);
        $this->post_body  = htmlspecialchars($post['body']);
        if(empty($this->post_title) || empty($this->post_body)) {
            $this->errors['post_form_err'] = "New post fields cannot be empty!";
        }
        if(FileManager::validateFile($file['image'], 5000000) === false) {
            $this->errors['post_file_err'] = "Invalid file!";
        }
        return $this;
    }

    public function createNewPost() {
        $this->post_img = FileManager::uploadFile();
        $this->post_user_id = $_SESSION['user_id'];
        $sql = "INSERT INTO posts (title, body, user_id, post_img) 
                VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssis", $this->post_title, $this->post_body, $this->post_user_id, $this->post_img);
        $stmt->execute();
        if($stmt->affected_rows !== 1) {
            $this->errors['insert_err'] = "Post was not created!";
        } else {
            $this->post_id = $stmt->insert_id;
        }
        return $this;
    }

    // success method, return T / F if $error is empty
    public function success() {
        if(empty($this->errors)) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id) {
       $sql = "DELETE FROM posts WHERE posts.id = ?";
       $stmt = $this->conn->prepare($sql);
       $stmt->bind_param("s", $id);
       $stmt->execute();
       if($stmt->affected_rows !== 1) {
           $this->errors['delete_err'] = "Failed to delete post!";
       } 
       return $this;
    }

    public function update($post) {
       $this->post_title = htmlspecialchars($post['title']);
       $this->post_body = htmlspecialchars($post['body']);
       $this->post_id = $post['post_id'];
       $sql = "UPDATE posts SET title = ? , body = ? WHERE posts.id = ?";
       var_dump($sql);
       var_dump($post);
       $stmt = $this->conn->prepare($sql);
       $stmt->bind_param("sss", $this->post_title, $this->post_body, $this->post_id);
       $stmt->execute();
       if($stmt->affected_rows !== 1) {
           $this->errors['update_err'] = "Failed to update post!";
       }
       return $this;
    }
}