<?php require_once 'classloader.php'; ?>

<?php 
if (!$userObj->isLoggedIn()) {
  header("Location: login.php");
}

if ($userObj->isAdmin()) {
  header("Location: ../admin/index.php");
}  
?>
<!doctype html>
  <html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <style>
      body {
        font-family: "Arial";
      }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Articles shared with you</div>
      <div class="row justify-content-center">
        <div class="col-md-8">
          <?php $articles = $articleObj->getSharedWithUser($_SESSION['user_id']); ?>
          <?php foreach ($articles as $article) { ?>
          <div class="card mt-4 shadow">
            <div class="card-body">
              <h1><?php echo $article['title']; ?></h1>
              <small><strong>Owner: <?php echo $article['username'] ?></strong> - <?php echo $article['created_at']; ?> </small>
              <?php if (!empty($article['image_path'])) { ?>
                <img src="<?php echo $article['image_path']; ?>" class="img-fluid mb-2" alt="Article image">
              <?php } ?>
              <p><?php echo $article['content']; ?></p>
              <hr>
              <h5>Edit this shared article</h5>
              <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
                <div class="form-group mt-2">
                  <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($article['title']); ?>">
                </div>
                <div class="form-group">
                  <textarea name="description" class="form-control"><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
                <div class="form-group">
                  <input type="file" class="form-control-file" name="image" accept="image/*">
                </div>
                <input type="hidden" name="article_id" value="<?php echo (int)$article['article_id']; ?>">
                <button class="btn btn-primary" name="editArticleBtn" type="submit">Save Changes</button>
              </form>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </body>
  </html>

