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
      body { font-family: "Arial"; background: #fffef7; }
      .card { border-radius: 16px; }
      .display-4 { font-weight: 700; }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Hello there and welcome! <span class="text-success"><?php echo $_SESSION['username']; ?></span>. Here are all the articles</div>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <input type="text" class="form-control mt-4" name="title" placeholder="Input title here">
            </div>
            <div class="form-group">
              <textarea name="description" class="form-control mt-4" placeholder="Submit an article!"></textarea>
            </div>
            <?php $categories = $articleObj->getCategories(); ?>
            <div class="form-group">
              <label>Category</label>
              <select class="form-control" name="category_id">
                <option value="">Select category</option>
                <?php foreach ($categories as $cat) { ?>
                  <option value="<?php echo (int)$cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <input type="file" class="form-control-file" name="image" accept="image/*">
            </div>
            <input type="submit" class="btn btn-primary form-control float-right mt-4 mb-4" name="insertArticleBtn">
          </form>
          <?php $articles = $articleObj->getActiveArticles(); ?>
          <?php foreach ($articles as $article) { ?>
          <div class="card mt-4 shadow">
            <div class="card-body">
              <h1><?php echo $article['title']; ?></h1> 
              <?php if ($article['is_admin'] == 1) { ?>
                <p><small class="bg-primary text-white p-1">  
                  Message From Admin
                </small></p>
              <?php } ?>
              <small><strong><?php echo $article['username'] ?></strong> - <?php echo $article['created_at']; ?> </small>
              <?php if (!empty($article['image_path'])) { ?>
                <img src="<?php echo $article['image_path']; ?>" class="img-fluid mb-2" alt="Article image">
              <?php } ?>
              <?php if (!empty($article['image_path'])) { ?>
                <img src="writer/<?php echo $article['image_path']; ?>" class="img-fluid mb-2" alt="Article image">
              <?php } ?>
              <?php if (!empty($article['category_name'])) { ?>
                <p><span class="badge badge-info">Category: <?php echo htmlspecialchars($article['category_name']); ?></span></p>
              <?php } ?>
              <p><?php echo $article['content']; ?> </p>
              <?php if ($article['author_id'] != $_SESSION['user_id']) { ?>
                <form action="core/handleForms.php" method="POST">
                  <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                  <?php 
                    $alreadyPending = $articleObj->hasPendingEditRequest((int)$article['article_id'], (int)$_SESSION['user_id']);
                    $alreadyShared = $articleObj->userHasEditAccess((int)$_SESSION['user_id'], (int)$article['article_id']);
                  ?>
                  <?php if (!$alreadyPending && !$alreadyShared) { ?>
                    <button class="btn btn-warning btn-sm" name="requestEditBtn">Request edit access</button>
                  <?php } elseif ($alreadyPending) { ?>
                    <button class="btn btn-secondary btn-sm" disabled>Request pending</button>
                  <?php } else { ?>
                    <button class="btn btn-success btn-sm" disabled>Access granted</button>
                  <?php } ?>
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