<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: index.php');
}

function getRandomString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
  
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['phone'])) {
        if (is_string($_POST['username']) && is_string($_POST['password']) && is_string($_POST['email']) && is_string($_POST['phone'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            if (strlen($username) > 0 && strlen($password) > 0 && strlen($email) > 0 && strlen($phone) > 0) {
                $db = new SQLite3('/db/sqlite.db');
                $stmt = $db->prepare('SELECT * FROM user_xml WHERE username=:username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                $result = $result->fetchArray(SQLITE3_ASSOC);
                if ($result === false) {
                    $md5_password = md5($password);
                    $xml_path = getRandomString(10).'_'.bin2hex($username).'_'.getRandomString(5).'.xml';
                    $xml = <<<XML
                    <?xml version="1.0" encoding="UTF-8"?>
                    <root>
                        <username>$username</username>
                        <password>$md5_password</password>
                        <type>normal</type>
                        <key></key>
                        <email>$email</email>
                        <phone>$phone</phone>
                    </root>
                    XML;
                    $file = fopen("/xml/".$xml_path, "w");
                    fwrite($file, $xml);
                    fclose($file);
                    $stmt = $db->prepare('INSERT INTO user_xml(username, xml_path) VALUES (:username, :xml_path)');
                    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                    $stmt->bindValue(':xml_path', $xml_path, SQLITE3_TEXT);
                    $stmt->execute();
                    $success = true;
                }
                else {
                    $error = "Username already exists";
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
            <form action="/register.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" placeholder="Username" id="username" name="username" maxlength="10" />
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
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" placeholder="Email" id="email" name="email"/>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="text" class="form-control" placeholder="Phone number" id="phone" name="phone"/>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <?php if (isset($error)) { ?>
            <div class="rows">
                <div class="alert alert-danger mt-2" role="alert"><?php echo $error; ?></div>
            </div>
            <?php } ?>

            <?php if (isset($success)) { ?>
            <div class="rows">
                <div class="alert alert-success mt-2" role="alert">Registration successful</div>
            </div>
            <?php } ?>
        </div>
    </div>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
