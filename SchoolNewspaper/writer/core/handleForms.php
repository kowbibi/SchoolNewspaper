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
			header("Location: ../index.php");
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

if (isset($_POST['insertArticleBtn'])) {
	$title = $_POST['title'];
	$description = $_POST['description'];
	$author_id = $_SESSION['user_id'];
	$image_path = null;
	if (!empty($_FILES['image']['name'])) {
		$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}
		$filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $_FILES['image']['name']);
		$target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
		if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
			$image_path = 'writer/uploads/' . $filename;
		}
	}
	if ($articleObj->createArticle($title, $description, $author_id, $image_path)) {
		header("Location: ../index.php");
	}
}

if (isset($_POST['editArticleBtn'])) {
	$title = $_POST['title'];
	$description = $_POST['description'];
	$article_id = $_POST['article_id'];
	// Only allow if owner or shared access
	$canEdit = false;
	if (isset($_SESSION['user_id'])) {
		$canEdit = $articleObj->userHasEditAccess((int)$_SESSION['user_id'], (int)$article_id);
	}
	if (!$canEdit) {
		$_SESSION['message'] = 'You do not have permission to edit this article.';
		$_SESSION['status'] = '400';
		header("Location: ../articles_submitted.php");
		exit;
	}
	$image_path = null;
	if (!empty($_FILES['image']['name'])) {
		$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir, 0777, true);
		}
		$filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $_FILES['image']['name']);
		$target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
		if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
			$image_path = 'writer/uploads/' . $filename;
		}
	}
	if ($articleObj->updateArticle($article_id, $title, $description, $image_path)) {
		header("Location: ../articles_submitted.php");
	}
}

if (isset($_POST['deleteArticleBtn'])) {
	$article_id = $_POST['article_id'];
	echo $articleObj->deleteArticle($article_id);
}

// Request edit access to an article
if (isset($_POST['requestEditBtn'])) {
	$article_id = (int)$_POST['article_id'];
	$article = $articleObj->getArticles($article_id);
	if ($article) {
		$owner_id = (int)$article['author_id'];
		$requester_id = (int)$_SESSION['user_id'];
		if ($owner_id !== $requester_id) {
			$articleObj->requestEditAccess($article_id, $requester_id, $owner_id);
			$articleObj->createNotification($owner_id, 'Edit Request', 'A writer requested edit access to your article: ' . $article['title']);
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