<?php  
require_once '../classloader.php';

if (isset($_POST['insertNewUserBtn'])) {
	$username = htmlspecialchars(trim($_POST['username']));
	$email = htmlspecialchars(trim($_POST['email']));
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {

		if ($password == $confirm_password) {

			if (!$userObj->usernameExists($username)) {

				if ($userObj->registerUser($username, $email, $password)) {
					header("Location: ../login.php");
				}

				else {
					$_SESSION['message'] = "An error occured with the query!";
					$_SESSION['status'] = '400';
					header("Location: ../register.php");
				}
			}

			else {
				$_SESSION['message'] = $username . " as username is already taken";
				$_SESSION['status'] = '400';
				header("Location: ../register.php");
			}
		}
		else {
			$_SESSION['message'] = "Please make sure both passwords are equal";
			$_SESSION['status'] = '400';
			header("Location: ../register.php");
		}
	}
	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}
}

if (isset($_POST['loginUserBtn'])) {
	$email = trim($_POST['email']);
	$password = trim($_POST['password']);

	if (!empty($email) && !empty($password)) {

		if ($userObj->loginUser($email, $password)) {
			// Redirect based on user type (we are in admin/core)
			if ($userObj->isAdmin()) {
				header("Location: ../index.php");
			} else {
				header("Location: ../../writer/index.php");
			}
		}
		else {
			$_SESSION['message'] = "Username/password invalid";
			$_SESSION['status'] = "400";
			header("Location: ../login.php");
		}
	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../login.php");
	}

}

if (isset($_GET['logoutUserBtn'])) {
	$userObj->logout();
	header("Location: ../index.php");
}

if (isset($_POST['insertAdminArticleBtn'])) {
	$title = $_POST['title'];
	$description = $_POST['description'];
	$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
	$author_id = $_SESSION['user_id'];
	$image_path = null;
	if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
		$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}
		$filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $_FILES['image']['name']);
		$target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
		if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
			$image_path = 'admin/uploads/' . $filename;
		}
	}
	if ($articleObj->createArticle($title, $description, $author_id, $image_path, $category_id)) {
		header("Location: ../index.php");
		exit;
	} else {
		$_SESSION['message'] = "Failed to create article. Please try again.";
		$_SESSION['status'] = '400';
		header("Location: ../index.php");
		exit;
	}

}

if (isset($_POST['editArticleBtn'])) {
	$title = $_POST['title'];
	$description = $_POST['description'];
	$article_id = $_POST['article_id'];
	$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
	$image_path = null;
	if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
		$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}
		$filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $_FILES['image']['name']);
		$target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
		if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
			$image_path = 'admin/uploads/' . $filename;
		}
	}
	if ($articleObj->updateArticle($article_id, $title, $description, $image_path, $category_id)) {
		header("Location: ../articles_submitted.php");
		exit;
	} else {
		$_SESSION['message'] = "Failed to update article. Please try again.";
		$_SESSION['status'] = '400';
		header("Location: ../articles_submitted.php");
		exit;
	}
}

if (isset($_POST['deleteArticleBtn'])) {
    if (!$userObj->isAdmin()) { header("Location: ../index.php"); exit; }
    $article_id = (int)$_POST['article_id'];
	$article = $articleObj->getArticles($article_id);
	if ($article) {
		$articleObj->createNotification((int)$article['author_id'], 'Article Deleted', 'Your article was deleted: ' . $article['title']);
	}
    $deleted = $articleObj->deleteArticle($article_id);
    // If AJAX, return 1/0; otherwise redirect with flash
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo $deleted ? 1 : 0;
        exit;
    }
    $_SESSION['message'] = $deleted ? 'Article deleted successfully.' : 'Deletion failed.';
    $_SESSION['status'] = $deleted ? '200' : '400';
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['updateArticleVisibility'])) {
	$article_id = $_POST['article_id'];
	$status = $_POST['status'];
	echo $articleObj->updateArticleVisibility($article_id,$status);
}

// Manage Categories (Admin only)
if (isset($_POST['insertCategoryBtn'])) {
    if (!$userObj->isAdmin()) { header("Location: ../index.php"); exit; }
    $name = trim($_POST['category_name']);
    if ($name !== '') {
        $articleObj->createCategory($name);
    }
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['deleteCategoryBtn'])) {
    if (!$userObj->isAdmin()) { header("Location: ../index.php"); exit; }
    $category_id = (int)$_POST['category_id'];
    if ($category_id) {
        $articleObj->deleteCategory($category_id);
    }
    header("Location: ../index.php");
    exit;
}

// Request edit access (admin can also request)
if (isset($_POST['requestEditBtn'])) {
	$article_id = (int)$_POST['article_id'];
	$article = $articleObj->getArticles($article_id);
	if ($article) {
		$owner_id = (int)$article['author_id'];
		$requester_id = (int)$_SESSION['user_id'];
		if ($owner_id !== $requester_id) {
			$articleObj->requestEditAccess($article_id, $requester_id, $owner_id);
			$articleObj->createNotification($owner_id, 'Edit Request', 'A user requested edit access to your article: ' . $article['title']);
		}
	}
	header("Location: ../index.php");
}

// Respond to an edit request (owner action)
if (isset($_POST['respondEditBtn'])) {
	$request_id = (int)$_POST['request_id'];
	$article_id = (int)$_POST['article_id'];
	$requester_id = (int)$_POST['requester_id'];
	$decision = $_POST['decision'] === 'accept';
	$owner_id = (int)$_SESSION['user_id'];
	$articleObj->respondToEditRequest($request_id, $decision, $article_id, $requester_id, $owner_id);
	$article = $articleObj->getArticles($article_id);
	$articleObj->createNotification($requester_id, 'Edit Request ' . ($decision ? 'Accepted' : 'Rejected'), 'Your request for article: ' . ($article ? $article['title'] : ('#' . $article_id)) . ' was ' . ($decision ? 'accepted' : 'rejected'));
	header("Location: ../articles_submitted.php");
}

// Fallback: prevent blank page when accessed directly or when no action matched
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	header("Location: ../index.php");
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	header("Location: ../index.php");
	exit;
}

