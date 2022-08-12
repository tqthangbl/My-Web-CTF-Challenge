<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if (is_string($_POST['username']) && is_string($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            if (strlen($username) > 0 && strlen($password) > 0) {
                $db = new SQLite3('/db/sqlite.db');
                $stmt = $db->prepare('SELECT * FROM user_xml WHERE username=:username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                $result = $result->fetchArray(SQLITE3_ASSOC);
                if ($result === false) {
                    $error = "User not found";
                }
                else {
                    $xml_path = $result['xml_path'];
                    $xml = file_get_contents("/xml/".$xml_path);
                    $dom = new DOMDocument();
                    $dom->loadXML($xml);
                    $info = simplexml_import_dom($dom);
                    $md5_password = md5($password);
                    if ($md5_password !== $info->password->__toString()) {
                        $error = "Wrong password";
                    }
                    else {
                        $_SESSION['username'] = $username;
                        $_SESSION['xml_path'] = $xml_path;
                        $_SESSION['type'] = $info->type->__toString();
                        $_SESSION['key'] = $info->key->__toString();
                        header('Location: index.php');
                    }
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
            <a href="/login.php" class="btn btn-success">Login</a>
            <a href="/register.php" class="btn btn-primary" style="margin-left: 10px;">Register</a>
          </div>
        </div>
      </div>
    </nav>
    <div class="content">
    <div class="container">
        <form action="/login.php" method="POST">
            <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" placeholder="Username" id="username" name="username" />
            </div>
            <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                class="form-control"
                placeholder="Password"
                id="password"
                name="password"
            />
            </div>
            <button type="submit" class="btn btn-primary" name="login">Log in</button>
        </form>
        <?php if (isset($error)) { ?>
        <div class="rows">
            <div class="alert alert-danger mt-2" role="alert"><?php echo $error; ?></div>
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
