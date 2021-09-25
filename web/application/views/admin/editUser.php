<script>
	function editGroups(uid){
		var isReimEle = document.getElementById("1");
		var isReimDirEle = document.getElementById("2");
		var inCapswarmEle = document.getElementById("3");
		var isReim = 0;
		var isReimDir = 0;
		var inCapswarm = 0;
		
		if(isReimEle.checked){
			isReim = 1;
		}
		if(isReimDirEle.checked){
			isReimDir = 1;
		}
		if(inCapswarmEle.checked){
			inCapswarm = 1;
		}
		
		$.ajax({
			url: "<?php echo base_url('admin/editGroups'); ?>",
			type: 'POST',
			data: {userID: uid, isReim: isReim, isReimDir: isReimDir, inCapswarm: inCapswarm},
			success: function(msg) {
				alert(msg);
				location.reload();
			}
		});
	}
	
	function resetPassword(uid) {
		var pw = document.getElementById("pass").value;
		
		$.ajax({
			url: "<?php echo base_url('admin/resetPassword'); ?>",
			type: 'POST',
			data: {userID: uid, password: pw},
			success: function(msg) {
				alert(msg);
			}
		});
	}
</script>
<div class="col-md-8 col-md-offset-1">
	<?php if($user->num_rows() > 0){ ?>
		<h3>Username: <?php echo $user->row(0)->user; ?></h3>
		<div class="row">
			<div class="col-md-4 col-md-offset-1">
				<h4>Groups</h4>
				<?php
					$gids = explode(",", $user->row(0)->gids);
					if(in_array(1,$gids)){
						$isReim = 1;
					} else {
						$isReim = 0;
					}
					if(in_array(2,$gids)){
						$isReimDir = 1;
					} else {
						$isReimDir = 0;
					}
					if(in_array(3,$gids)){
						$inCapswarm = 1;
					} else {
						$inCapswarm = 0;
					}
				?>
				<form>
					<input type="checkbox" name="isReim" value="Reimburser" id="1" <?php if($isReim == 1){ echo "checked";} ?>>Reimburser</input><br />
					<input type="checkbox" name="isReimDir" value="Reimbursement Director" id="2" <?php if($isReimDir == 1){ echo "checked";} ?>>Reimbursement Director</input><br />
					<input type="checkbox" name="inCapswarm" value="Capswarm" id="3" <?php if($inCapswarm == 1){ echo "checked";} ?>>Capswarm</input><br />
				</form>
				<br>
				<button class="btn btn-primary btn-sm" value="Edit Groups" onClick="editGroups('<?php echo $user->row(0)->id; ?>')">Edit Groups</button>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-4 col-md-offset-1">
				<h4>Reset Password</h4>
				<input type="text" name="pass" id="pass"></input><br /><br />
				<button class="btn btn-success btn-sm" onClick="resetPassword('<?php echo $user->row(0)->id; ?>')" value="Reset Password">Reset Password</button>
			</div>
		</div>
	<?php } else { ?>
		<h3>Congratulations! You have broken something. This user could not be found, please try again.</h3>
	<?php } ?>
</div>
