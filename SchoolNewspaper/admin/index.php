<?php require_once 'classloader.php'; ?>

<?php 
if (!$userObj->isLoggedIn()) {
  header("Location: login.php");
}

if (!$userObj->isAdmin()) {
  header("Location: ../writer/index.php");
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
      body { font-family: "Arial"; background: #f7fbff; }
      .card { border-radius: 16px; }
      .display-4 { font-weight: 700; }
      .badge-admin { background: #FF8A65; }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Welcome Admin 🧑‍🏫 <span class="text-success"><?php echo $_SESSION['username']; ?></span>!</div>
      <p class="text-center text-muted">Guide the school newsroom and keep stories kid-friendly.</p>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <input type="text" class="form-control mt-4" name="title" placeholder="Input title here">
            </div>
            <div class="form-group">
              <textarea name="description" class="form-control mt-4" placeholder="Message as admin"></textarea>
            </div>
            <div class="form-group">
              <input type="file" class="form-control-file" name="image" accept="image/*">
            </div>
            <input type="submit" class="btn btn-primary form-control float-right mt-4 mb-4" name="insertAdminArticleBtn">
          </form>
          <?php $articles = $articleObj->getActiveArticles(); ?>
          <?php foreach ($articles as $article) { ?>
          <div class="card mt-4 shadow">
            <div class="card-body">
              <h1><?php echo $article['title']; ?></h1> 
              <?php if ($article['is_admin'] == 1) { ?>
                <p><small class="badge badge-admin text-white p-1">Message From Admin</small></p>
              <?php } ?>
              <small><strong><?php echo $article['username'] ?></strong> - <?php echo $article['created_at']; ?> </small>
              <?php if (!empty($article['image_path'])) { ?>
                <img src="<?php echo '../' . $article['image_path']; ?>" class="img-fluid mb-2" alt="Article image">
              <?php } ?>
              <?php if (!empty($article['image_path'])) { ?>
                <img src="admin/<?php echo $article['image_path']; ?>" class="img-fluid mb-2" alt="Article image">
              <?php } ?>
              <p><?php echo $article['content']; ?> </p>
              <div class="mt-3 d-flex justify-content-end">
                <form class="mr-2" action="core/handleForms.php" method="POST" onsubmit="return confirm('Delete this article?');">
                  <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                  <button class="btn btn-danger btn-sm" name="deleteArticleBtn">Delete</button>
                </form>
              </div>
              <?php if ($article['author_id'] != $_SESSION['user_id']) { ?>
                <form action="core/handleForms.php" method="POST">
                  <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                  <button class="btn btn-warning btn-sm" name="requestEditBtn">Request edit access</button>
                </form>
              <?php } ?>
            </div>
          </div>  
          <?php } ?> 
        </div>
      </div>
    </div>
  </body>
</html>