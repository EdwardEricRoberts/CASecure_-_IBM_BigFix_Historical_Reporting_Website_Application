<div class="span-15" style="margin-top:-10px;">
			<nav id="primary_nav_wrap">
				<ul>
					<li id="dashboardNav"><a href="Dashboards.php">Dashboard</a></li>
					<li id="patchingNav"><a href="PatchingOverview.html">Patching</a>
						<ul>
							<li><a href="PatchingOverview.html">Overview</a></li>
							<li><a href="Devices.php">Devices</a></li>
						</ul>
					</li>
					<li id="complianceNav"><a href="ComplianceOverview.php">Compliance</a>
						<ul>
							<li class="dir"><a href="ComplianceOverview.php">Overview</a></li>
							<li class="dir"><a href="PCI.html">PCI</a>
								<ul>
									<li><a href="PCI-DSSWin2008.php">PCI-DSS Windows 2008</a></li>
									<li><a href="PCI-DSSWin7.php">PCI-DSS Windows 7</a></li>
								</ul>
							</li>
							<li class="dir"><a href="#">CIS</a></li>
							<li><a href="#">USGCB</a></li>
						</ul>
					</li>
					<li id="inventoryNav"><a href="#">Inventory</a></li>
					<li id="reportingNav"><a href="Reporting.html">Reporting</a></li>
					<li id="infoNav"><a href="#">Information</a>
						<ul>
							<li><a href="Requests.php">Requests</a></li>
							<li><a href="Enhancements.php">Enhancements</a></li>
							<li><a href="Messages.php">Messages</a></li>
						</ul>
					</li>
				</ul>
			</nav>
		<br>
</div>
<div class="prepend-6 span-2 last" style="margin-top:-5px;">

<!-- Modal Box -->
<div id="ex1" class="modal">
  <p>Are you sure you want to leave CASecure web portal?</p>
  <a href="Login.php" onmouseover="" style="cursor:pointer;"><button>Yes</button></a>
  <a href="#" rel="modal:close" onmouseover="" style="cursor:pointer;"><button>No</button></a>
</div>

<!-- Link to open the modal -->

<a href="#ex1" rel="modal:open"><img src="includes/SignOut.jpg" border="0"></a>
<br>
</div>
<script>
 $("#fade").modal({
  fadeDuration: 100,
  fadeDelay: 0.50
});
</script>
