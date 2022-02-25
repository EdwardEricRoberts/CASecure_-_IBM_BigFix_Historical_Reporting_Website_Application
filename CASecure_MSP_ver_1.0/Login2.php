<?php
require_once './includes/init.php';
include_once "./database/sessions/AutoLogin.php";

use database\sessions\AutoLogin;

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $pwd = trim($_POST['pwd']);
    $stmt = $db->prepare('SELECT password FROM users WHERE user_name = :username');
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $stored = $stmt->fetchColumn();
	//$userID = $stmt->fetchColumn(1);
	//$firstName = $stmt->fetchColumn(2);
    if (password_verify($pwd, $stored)) {
		$userSQL = 
			"SELECT u.welcome_name, u.user_id, u.is_admin, d.default_site, d.default_computer_group ".
			"FROM users u,  user_defaults d ".
			"WHERE ".
				"u.user_id = d.user_id AND ".
				"u.user_name = :username;";
		$userQuery = $db->prepare($userSQL);
		$userQuery->bindParam(':username', $username);
		$userQuery->execute();
		$userData = $userQuery->fetch(PDO::FETCH_ASSOC);
		$firstName = $userData['welcome_name'];//$userQuery->fetchColumn();
		$userID = $userData['user_id'];//$userQuery->fetchColumn(1);
		$adminStat = $userData['is_admin'];
		$dSite = $userData['default_site'];
		$dCompGroup = $userData['default_computer_group'];
		
		
		$bigfixSQL = 
			"SELECT bigfix_user_name, bigfix_password, bigfix_server ".
			"FROM bigfix_logins ".
			"WHERE confirmed = TRUE AND ".
				"bigfix_user_name = ".
				"(".
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :userID".
				") ;";
		$bigfixQuery = $db->prepare($bigfixSQL);
		$bigfixQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
		$bigfixQuery->execute();
		$bigfixData = $bigfixQuery->fetch(PDO::FETCH_ASSOC); //PDO::FETCH_NUMERIC
		$bfUser = $bigfixData['bigfix_user_name'];
		$bfPass = $bigfixData['bigfix_password'];
		$bfServ = $bigfixData['bigfix_server'];
		
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
		$_SESSION['userid'] = $userID;
		$_SESSION['firstname'] = $firstName;
		$_SESSION['admin'] = $adminStat;
		$_SESSION['defaultsite'] = $dSite;
		$_SESSION['defaultcomputergroup'] = $dCompGroup;
        $_SESSION['authenticated'] = true;
		$_SESSION['bigfixuser'] = $bfUser;
		$_SESSION['bigfixpassword'] = $bfPass;
		$_SESSION['bigfixserver'] = $bfServ;
        if (isset($_POST['remember'])) {
            // create persistent login
            $autologin = new AutoLogin($db);
            $autologin->persistentLogin();
        }
        header('Location: Dashboards.php');
        exit;
    } else {
        $error = 'Login failed. Check username and password.';
    }
}
?>

<html>
	<head>
		<title>CASecure >Login</title>
		
      <?php require 'includes/headerLogin.php'; ?>
      <div class="span-24" style="background-color:#185725; margin-top:4px;"> &nbsp;</div>
		<div class="container">
		
			<!-- <div class="banner2">
				<div class="span-6"><img src="includes/casecure_logo.jpg" class="logo2"></div>
				<div class="prepend-20 last">
					<div class="webportal2"><br>Web Portal</div>
				</div>
			</div> -->
			
			<br><br><br>
			<?php
				if (isset($error)) {
					echo "<p>$error</p>";
				}
			?>
			<div class="prepend-7">
				<div class="span-10">
					<div <!--class="login2"-->
						<div class="prepend-2 last"></div>
						<div class="loginprompttitle">Login</div><br>
						Please enter your username and password to connect.<br><br>
						<form action="" method="post" autocomplete="off">
							<label for="username">Username:</label><br>
							<input type="text" name="username" id="username" size="30"><br><br>
							<label for="pwd">Password:</label><br>	
							<input type="password" name="pwd" id="pwd" size="30" autocomplete="off"><br><br>
							<input type="checkbox" name="remember" id="remember">
							<label for="remember">Remember me </label><br><br>
							<input type="submit" name="login" id="login" value="Log In">
						</form>
						<!--<a href="Dashboards.php"><input type="submit" value="Submit"></a>-->
					</div>
			
				</div>
			</div>
		</div>
		
		
		<br><br><br><br><br><br>
	</body>
</html>