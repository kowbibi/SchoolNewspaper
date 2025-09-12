<?php  

require_once 'Database.php';
require_once 'User.php';
/**
 * Class for handling Article-related operations.
 * Inherits CRUD methods from the Database class.
 */
class Article extends Database {
    /**
     * Creates a new article.
     * @param string $title The article title.
     * @param string $content The article content.
     * @param int $author_id The ID of the author.
     * @return int The ID of the newly created article.
     */
    public function createArticle($title, $content, $author_id, $image_path = null) {
        $sql = "INSERT INTO articles (title, content, author_id, image_path, is_active) VALUES (?, ?, ?, ?, 0)";
        return $this->executeNonQuery($sql, [$title, $content, $author_id, $image_path]);
    }

    /**
     * Retrieves articles from the database.
     * @param int|null $id The article ID to retrieve, or null for all articles.
     * @return array
     */
    public function getArticles($id = null) {
        if ($id) {
            $sql = "SELECT * FROM articles WHERE article_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT * FROM articles JOIN school_publication_users ON articles.author_id = school_publication_users.user_id ORDER BY articles.created_at DESC";
        return $this->executeQuery($sql);
    }

    public function getActiveArticles($id = null) {
        if ($id) {
            $sql = "SELECT * FROM articles WHERE article_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT * FROM articles 
                JOIN school_publication_users ON 
                articles.author_id = school_publication_users.user_id 
                WHERE is_active = 1 ORDER BY articles.created_at DESC";
                
        return $this->executeQuery($sql);
    }

    public function getArticlesByUserID($user_id) {
        $sql = "SELECT * FROM articles 
                JOIN school_publication_users ON 
                articles.author_id = school_publication_users.user_id
                WHERE author_id = ? ORDER BY articles.created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    /**
     * Updates an article.
     * @param int $id The article ID to update.
     * @param string $title The new title.
     * @param string $content The new content.
     * @return int The number of affected rows.
     */
    public function updateArticle($id, $title, $content, $image_path = null) {
        if ($image_path !== null) {
            $sql = "UPDATE articles SET title = ?, content = ?, image_path = ? WHERE article_id = ?";
            return $this->executeNonQuery($sql, [$title, $content, $image_path, $id]);
        }
        $sql = "UPDATE articles SET title = ?, content = ? WHERE article_id = ?";
        return $this->executeNonQuery($sql, [$title, $content, $id]);
    }
    
    /**
     * Toggles the visibility (is_active status) of an article.
     * This operation is restricted to admin users only.
     * @param int $id The article ID to update.
     * @param bool $is_active The new visibility status.
     * @return int The number of affected rows.
     */
    public function updateArticleVisibility($id, $is_active) {
        $userModel = new User();
        if (!$userModel->isAdmin()) {
            return 0;
        }
        $sql = "UPDATE articles SET is_active = ? WHERE article_id = ?";
        return $this->executeNonQuery($sql, [(int)$is_active, $id]);
    }


    /**
     * Deletes an article.
     * @param int $id The article ID to delete.
     * @return int The number of affected rows.
     */
    public function deleteArticle($id) {
        $sql = "DELETE FROM articles WHERE article_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    public function createNotification($user_id, $title, $message) {
        $sql = "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)";
        return $this->executeNonQuery($sql, [$user_id, $title, $message]);
    }

    public function getNotifications($user_id) {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    public function requestEditAccess($article_id, $requester_id, $owner_id) {
        $sql = "INSERT INTO edit_requests (article_id, requester_id, owner_id) VALUES (?, ?, ?)";
        return $this->executeNonQuery($sql, [$article_id, $requester_id, $owner_id]);
    }

    public function respondToEditRequest($request_id, $accept, $article_id, $requester_id, $owner_id) {
        $status = $accept ? 'accepted' : 'rejected';
        $this->executeNonQuery("UPDATE edit_requests SET status = ? WHERE request_id = ?", [$status, $request_id]);
        if ($accept) {
            $this->executeNonQuery("INSERT INTO article_shares (article_id, shared_with_user_id, granted_by_user_id) VALUES (?, ?, ?)", [$article_id, $requester_id, $owner_id]);
        }
        return true;
    }

    public function getSharedWithUser($user_id) {
        $sql = "SELECT a.* , u.username FROM article_shares s JOIN articles a ON s.article_id = a.article_id JOIN school_publication_users u ON a.author_id = u.user_id WHERE s.shared_with_user_id = ? ORDER BY a.created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    public function getPendingEditRequestsByOwner($owner_id) {
        $sql = "SELECT r.request_id, r.article_id, r.requester_id, r.status, r.created_at, a.title, u.username AS requester_name
                FROM edit_requests r
                JOIN articles a ON r.article_id = a.article_id
                JOIN school_publication_users u ON r.requester_id = u.user_id
                WHERE r.owner_id = ? AND r.status = 'pending' ORDER BY r.created_at DESC";
        return $this->executeQuery($sql, [$owner_id]);
    }

    public function userHasEditAccess($user_id, $article_id) {
        // Owner check
        $article = $this->executeQuerySingle("SELECT author_id FROM articles WHERE article_id = ?", [$article_id]);
        if ($article && (int)$article['author_id'] === (int)$user_id) {
            return true;
        }
        // Shared access check
        $row = $this->executeQuerySingle(
            "SELECT share_id FROM article_shares WHERE article_id = ? AND shared_with_user_id = ?",
            [$article_id, $user_id]
        );
        return !empty($row);
    }
}
?>