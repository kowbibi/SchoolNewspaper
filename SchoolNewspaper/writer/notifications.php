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
      body { font-family: "Arial"; }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Notifications</div>
      <div class="row justify-content-center">
        <div class="col-md-8">
          <?php $notifs = $articleObj->getNotifications($_SESSION['user_id']); ?>
          <?php foreach ($notifs as $n) { ?>
            <div class="card mt-3">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($n['title']); ?></h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($n['message'])); ?></p>
                <small class="text-muted"><?php echo $n['created_at']; ?></small>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </body>
  </html>

