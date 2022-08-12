<?php
include("config.php");

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
}

if ($_SESSION['type'] === "normal") {
  $words = json_decode(file_get_contents("http://iz-vocabulary-api:8080/normal"));
}
else if ($_SESSION['type'] == "vip") {
  if ($under_maintenance === false) {
    $words = json_decode(file_get_contents("http://iz-vocabulary-api:8080/vip/".$_SESSION['username']."/".$_SESSION['key']));
  }
  else {
    if ($_SESSION['username'] === "admin") {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "http://iz-vocabulary-api:8080/debug");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      var_dump(curl_exec($ch));
      curl_close($ch);
    }
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <title>IzVocabulary</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-3">
      <div class="container-fluid">
        <a class="navbar-brand" href="/">IzVocabulary</a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <div class="d-flex">
            <a class="nav-link" style="color: #fff" href="#">Hello, <?php echo $_SESSION['username']; ?> </a>
            <a href="/setting.php" class="btn btn-primary">Setting</a>
            <a href="/logout.php" class="btn btn-secondary" style="margin-left: 10px;">Logout</a>
          </div>
        </div>
      </div>
    </nav>
    <div class="content">
      <div class="container">
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php 
              $cnt = 0;
              foreach ($words as $word) {
                $cnt++;
            ?>
              <div class="carousel-item <?php if ($cnt == 1) echo "active"; ?>">
              <center>
              <div class="col-sm-10 col-sm-offset-1">
              <div class="col-md-4 col-sm-6">
                <div class="card-container">
                  <div class="card">
                    <div class="front">
                      <div class="header">
                        <h5 class="motto">Hover this card to view meaning of word</h5>
                      </div>
                      <div class="content">
                        <div class="main">
                          <h2 class="text-center"><?php echo $word->word; ?></h2>
                        </div>
                      </div>
                    </div>
                    <!-- end front panel -->
                    <div class="back">
                      <img src="<?php echo $word->image; ?>" />
                      <div class="content">
                        <div class="main">
                          <h3 class="name"><?php echo $word->word; ?></h3>
                          <p>
                            <i><?php echo $word->type; ?></i>
                          </p>
                          <p><?php echo $word->pronoun; ?></p>
                          <p class="text-center"><?php echo $word->meaning; ?></p>
                        </div>
                      </div>
                    </div>
                    <!-- end back panel -->
                  </div>
                  <!-- end card -->
                </div>
                <!-- end card-container -->
              </div>
              <!-- end col-sm-3 -->
            </div>
            <!-- end col-sm-10 -->
              </center>
            </div>
            <?php } ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
        
        <?php if ($_SESSION['type'] == "normal") { ?>
        <div class="rows">
            <div class="alert alert-primary mt-2" role="alert">Need to learn more word? <a href="/upgrade.php" target="_blank">Upgrade your account</a> now!</div>
        </div>
        <?php } ?>
      </div>

    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="/assets/js/script.js"></script>
  </body>
</html>