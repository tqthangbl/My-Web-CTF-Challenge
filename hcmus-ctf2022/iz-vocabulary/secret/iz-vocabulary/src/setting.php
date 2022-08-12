<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['username'] === "admin") {
      $error = "Error";
    } else {
      if (isset($_POST['email']) && isset($_POST['phone'])) {
          if (is_string($_POST['email']) && is_string($_POST['phone'])) {
              $email = $_POST['email'];
              $phone = $_POST['phone'];
              if (strlen($email) > 0 && strlen($phone) > 0) {
                if (stripos($email, "http://") === false && stripos($phone, "http://") === false
                  && stripos($email, "https://") === false && stripos($phone, "https://") === false) {
                    $xml = file_get_contents("/xml/".$_SESSION['xml_path']);
                    $dom = new DOMDocument();
                    $dom->loadXML($xml);
                    $info = simplexml_import_dom($dom);
                    $xml = <<<XML
                        <?xml version="1.0" encoding="UTF-8"?>
                        <root>
                            <username>$info->username</username>
                            <password>$info->password</password>
                            <type>$info->type</type>
                            <key>$info->key</key>
                            <email>$email</email>
                            <phone>$phone</phone>
                        </root>
                        XML;
                    $file = fopen("/xml/".$_SESSION['xml_path'], "w");
                    fwrite($file, $xml);
                    fclose($file);
                    $success = true;
                }
                else {
                  $error = "No hack";
                }
              }
              else {
                  $error = "Please fill out all fields";
              }
          }
          else {
              $error = "No hack";
          }
      }
      else {
          $error = "Please fill out all fields";
      }
    }
}

$xml = file_get_contents("/xml/".$_SESSION['xml_path']);
$dom = new DOMDocument();
$dom->loadXML($xml);
$dom->xinclude();
$info = simplexml_import_dom($dom);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3"
      crossorigin="anonymous"
    />

    <title>IzVocabulary</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-3">
      <div class="container-fluid">
        <a class="navbar-brand" href="/">IzVocabulary</a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <div class="d-flex">
            <li class="nav-item">
              <a class="nav-link" style="color: #fff" href="#">Hello, <?php echo $_SESSION['username']; ?></a>
            </li>
            <a href="/setting.php" class="btn btn-primary">Setting</a>
            <a href="/logout.php" class="btn btn-secondary" style="margin-left: 10px;">Logout</a>
          </div>
        </div>
      </div>
    </nav>
    <div class="content">
        <div class="container">
            <form action="/setting.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="<?php echo $info->email; ?>" />
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $info->phone; ?>" />
                </div>
                <a href="/info.php" target="_blank" class="btn btn-primary">Download information</a>
                <button type="submit" class="btn btn-success">Update information</button>
            </form>

            <?php if (isset($error)) { ?>
            <div class="rows">
                <div class="alert alert-danger mt-2" role="alert"><?php echo $error; ?></div>
            </div>
            <?php } ?>

            <?php if (isset($success)) { ?>
            <div class="rows">
                <div class="alert alert-success mt-2" role="alert">Information updated</div>
            </div>
            <?php } ?>
        </div>
    </div>
    </div>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
