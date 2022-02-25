<form action="logout.php" method="post" id="logout_form" style="display:inline; color:#185725;">
	<input type="submit"  name="logout" value="Sign Out">
	<!--<a href="javascript:{}" onclick="document.getElementById('logout_form').submit(); return false;">Sign out</a>-->
    <?php $_SESSION['return_to'] = $_SERVER['PHP_SELF']; ?>
</form>
