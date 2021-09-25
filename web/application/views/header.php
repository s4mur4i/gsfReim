<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<title><?php echo $this->config->item("REIM_NAME"); ?></title>
	<script src='https://code.jquery.com/jquery-2.1.1.min.js' type="text/javascript"></script>
	<script src="/assets/js/tether.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous"> 
	<!--<link rel="stylesheet" href="/assets/css/bootstrap.css">-->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	

	<link rel="stylesheet" href="/assets/css/jquery-ui.autocomplete.css" />
	<link rel="stylesheet" href="/assets/css/jquery-ui.theme.min.css" />
	<link rel="stylesheet" href="/assets/css/jquery-ui.structure.min.css" />
	<link rel="stylesheet" href="/assets/css/jquery-ui.min.css" />
	<script src="/assets/js/jquery-ui.min.js" type="text/javascript"></script>
	<style>
		.ui-datepicker{z-index:115111 !important;}
		.ui-autocomplete{z-index:115111 !important;}
		.navbar-collapse { margin-bottom: -15px; }
		.navbar {
			margin-bottom: 10px;
			background-color: #666666;
		}
		.navbar-light .navbar-brand, .navbar-light .navbar-toggler {
			color: rgb(232, 232, 232);
		}
		.navbar-light .navbar-nav .nav-link { color: rgb(232, 232, 232); }
		.navbar-light .navbar-text { color: rgb(232,232,232); }
		.modal-lg { max-width: 1000px; }
		body {
			font-size: 0.9rem;
		}
	</style>

</head>

<script type="text/javascript">
$(document).ready(function() {
	$("#submitKillShow").click(function(){
		$("#submitKill").modal('show');
	})
	$("#banUserShow").click(function(){
		$("#banUser").modal('show');
	})
});

	function clearSubmitKill() {
		var skClone = document.getElementById("submitKillBodyCopy")
		$("#submitKillBody").html(skClone.innerHTML)
	}

	function submitKill() {
		var crestLink = document.getElementById("crestLink").value;
		var bcast = document.getElementById("bcast").value;

		if(crestLink==null || crestLink==""){
			alert("You must enter a CREST Link");
		} else {
			$('#submitKillBody').html('<center><img src="/assets/img/ajax-loader.gif" /></center>')
			$.ajax({
				url: "<?php echo base_url('home/submitKill'); ?>",
				type: 'POST',
				data: {crestLink: crestLink, bcast: bcast},
				success: function(msg) {
					$('#submitKillBody').html(msg);
				}
			});
		}
	}
</script>
<?php if($this->config->item("AUTH_METHOD") == "INTERNAL" && $this->config->item("ALLOW_REGISTRATION")){ ?>
	<script>
		$(document).ready(function() {
			$("#registerAccount").click(function(){
				$("#regUser").modal('show');
			})
		});
		function registerUser(){
			var user = document.getElementById("regUserName").value;
			var pass = document.getElementById("regUserPassword").value;
			$.ajax({
				url: "<?php echo base_url('login/register'); ?>",
				type: 'POST',
				data: {user: user, password: pass},
				success: function(msg) {
					$('#regUserBody').html(msg);
					if(msg == "User Exists. Please refresh the page and try again, or wait 3 seconds for the page to automatically refresh."){
						setTimeout(function(){ location.reload(1); }, 3000);
					}
				}
			});
		}
	</script>
<?php } ?>
<?php
$vars = $this->session->userdata('vars');
if($vars['isReimDir'] == 1){?>
	<script>
		$(document).ready(function() {
			$("#banUserShow").click(function(){
				$("#banUser").modal('show');
			});
			$('#banUser').on('hidden.bs.modal', function () {
				location.reload();
			});
			$("#banUserName").autocomplete({
				source: "<?php echo base_url('/admin/getUserAutocomplete'); ?>",
				cacheLength: 1,
				minLength: 3
			});
			$("#banUserEndDate").datepicker({
				minDate: '+1d',
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true
			});
		});

		function banUser() {
			var userName = document.getElementById("banUserName").value;
			var reason = document.getElementById("banUserReason").value;
			var endDate = document.getElementById("banUserEndDate").value;

//			alert(userName+" | "+reason+" | "+endDate);
			if(userName=='' || reason=='' || endDate==''){
				alert("You must specify a user, reason, and end date.");
			} else {
				$.ajax({
					url: "<?php echo base_url('admin/banUser'); ?>",
					type: 'POST',
					data: {userName: userName, reason: reason, expiry: endDate},
					success: function(msg) {
						$('#banUserBody').html(msg);
					}
				});
			}
		}
	</script>
<?php } ?>
<body>
	<nav class="navbar navbar-toggleable-md navbar-light">
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
	    <a class="navbar-brand" href="<?php echo base_url('home'); ?>"><?php echo $this->config->item("REIM_NAME"); ?></a>
	    <!-- Collect the nav links, forms, and other content for toggling -->
	    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
	      <ul class="navbar-nav mr-auto">
			<li class="nav-item"><a href="https://goonfleet.com" class="nav-link">Back to Forums</a></li>
	        <li class="nav-item"><?php echo anchor('home', 'Home', 'class="nav-link"'); ?></a></li>
	        <li class="nav-item"><?php echo anchor("home/viewPayouts", 'View Payouts', 'class="nav-link"'); ?></li>
	        <?php if($vars['logged_in'] && $vars['isBanned'] == 0){ ?>
	        	<li class="nav-item"><?php echo anchor("reports/insurance", "Insurance", 'class="nav-link"'); ?></li>
		        <li class="nav-item"><a href="#" id="submitKillShow" class="nav-link">Submit Loss</a></li>
		        <li class="nav-item"><?php echo anchor('home/mySubmitted', "My Losses", 'class="nav-link"'); ?></li>
		        <li class="nav-item"><?php echo anchor('reports', 'Reports', 'class="nav-link"'); ?></li>
		        <?php if($vars['isReim'] == 1 || $vars['isReimDir'] == 1){ ?>
		        	<li class="nav-item"><?php echo anchor('home/myReserved', 'Reserve Block', 'class="nav-link"'); ?></li>
		        <?php } ?>
		        <?php if($vars['isReimDir'] == 1 || $vars['isReim'] == 1){ ?>
			        <li class="nav-item dropdown">
			        	<a href="#" class="nav-link dropdown-toggle" id="admin-menu" data-toggle="dropdown" role="button" aria-expanded="false">Admin <span class="caret"></span></a>
			        	<div class="dropdown-menu" aria-labelledby="admin-menu" style="margin-top: -10px;">
			        		<?php if($vars['isReimDir'] == 1){ ?>
			        		<?php echo anchor('admin/regionMgmt','Region Management', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/payoutMgmt', 'Payout Management', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/payoutTypes', 'Payout Types', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/viewReserved', 'View Reserved Losses', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/viewBanned', 'View Banned Users', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/viewSettings', 'Preferences', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/viewLog', 'View Logs', 'class="dropdown-item"'); ?>
			        		<?php echo anchor('admin/viewGroupBans', "Group Bans", 'class="dropdown-item"'); ?>
			        		<?php if($this->config->item('AUTH_METHOD') == "INTERNAL"){ ?>
			        			<?php echo anchor('admin/userMgmt', "User Management", 'class="dropdown-item"'); ?>
			        		<?php } ?>
			        		<?php } ?>
			        		<?php echo anchor('admin/viewDenied', 'View Denied Payouts', 'class="dropdown-item"'); ?>
			        	</div>
			        </li>
			        <?php if($vars['isReimDir'] == 1){ ?>
			        <li class="nav-item"><a href="#" id="banUserShow" class="nav-link">Ban User</a></li>
			    <?php }
					} ?>

		     <?php } ?>
		     </ul>
			<ul class="navbar-form navbar-right">
				<?php if (!$vars['logged_in']) { ?>
				<form class="form-inline" action="<?php echo base_url('login'); ?>" method="post" accept-charset="utf-8">
					<input class="form-control mr-sm-2" type="text" id="user" name="user" placeholder="User Name">
					<input class="form-control mr-sm-2" type="password" id="password" name="password" placeholder="Password">
					<button type="submit" class="btn btn-outline-info my-2 my-sm-0">Login</button>
					<?php
					if($this->config->item('AUTH_METHOD') == "INTERNAL" && $this->config->item("ALLOW_REGISTRATION")){ ?>
						<button type="button" class="btn btn-outline-success my-2 my-sm-0" id="registerAccount" value="Register">Register</button>
					<?php } ?>
					</form>

				<?php } else { ?>
					<span class="navbar-text" style="padding-right: 5px;">Welcome, <?php echo $vars['user']; ?></span>
					<?php echo anchor('login/logout','Logout', 'class="btn btn-outline-danger"');
				} ?>
			</ul>
	    </div><!-- /.navbar-collapse -->
	</nav>
<div class="modal fade" id="submitKill" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Submit Kill <button type="button" class="btn btn-outline-danger" onclick="clearSubmitKill()" value="Reset">Reset</button></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body" id="submitKillBody">
		<div class="input-group">
			<span class="input-group-addon">CREST Link or ESI Link</span>
			<?php echo form_input(array('name' => 'crestLink', 'id' => 'crestLink', 'class' => 'form-control')); ?>
		</div>
		<br>
		<div class="input-group">
			<span class="input-group-addon">Broadcast or Op Post</span>
			<?php echo form_textarea(array('name' => 'bcast', 'id' => 'bcast', 'class' => 'form-control')); ?>
		</div>
		<br>
		<span><?php echo form_submit(array('name' => 'submit', 'id' => 'submit', 'value' => 'Submit', 'class' => 'btn btn-outline-success', 'onclick' => 'submitKill()')); ?></span>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div style="display: none;">
	<div class="modal-body" id="submitKillBodyCopy">
		<div class="input-group">
			<span class="input-group-addon">CREST Link or ESI Link</span>
			<?php echo form_input(array('name' => 'crestLink', 'id' => 'crestLink', 'class' => 'form-control')); ?>
		</div>
		<br>
		<div class="input-group">
			<span class="input-group-addon">Broadcast or Op Post</span>
			<?php echo form_textarea(array('name' => 'bcast', 'id' => 'bcast', 'class' => 'form-control')); ?>
		</div>
		<br>
		<span><?php echo form_submit(array('name' => 'submit', 'id' => 'submit', 'value' => 'Submit', 'class' => 'btn btn-outline-success', 'onclick' => 'submitKill()')); ?></span>
	</div>
</div>
<?php if($vars["isReimDir"] == 1){ ?>
	<div class="modal fade" id="banUser" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog modal-lg" style="width: 50%">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Ban User</h4>
	      </div>
	      <div class="modal-body" id="banUserBody">
			<div class="input-group">
				<span class="input-group-addon">User Name</span>
				<input type="text" class="form-control" id="banUserName" name="banUserName"></input>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Reason</span>
				<textarea class="form-control" id="banUserReason" rows="5"></textarea>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Ban End Date</span>
				<input type="text" class="form-control" id="banUserEndDate"></input>
			</div>
			<br>
			<button btype="button" class="btn btn-outline-warning" onclick="banUser()" value="Ban User">Ban User</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
<?php } ?>
<?php if($this->config->item("AUTH_METHOD") == "INTERNAL"){ ?>
		<div class="modal fade" id="regUser" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog modal-lg" style="width: 50%">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">Register</h4>
	      </div>
	      <div class="modal-body" id="regUserBody">
			<div class="input-group">
				<span class="input-group-addon">User Name</span>
				<input type="text" class="form-control" id="regUserName" name="regUserName"></input>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Password</span>
				<input type="password" class="form-control" id="regUserPassword"></input>
			</div>
			<br>
			<button btype="button" class="btn btn-outline-success" onclick="registerUser()" value="Register">Register</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
<?php } ?>
<div>
