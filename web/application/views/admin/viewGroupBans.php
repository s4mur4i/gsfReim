<script>
	function unBan(id){
		$.ajax({
			url: "<?php echo base_url('admin/unbanGroup'); ?>",
			type: 'POST',
			data: {banID: id},
			success: function(msg) {
				if (msg.length > 0) {
					alert(msg);
				}
				location.reload();
			}
		});
	}

	function banGroup(){
		var groupName = document.getElementById("groupName").value;
		var reason = document.getElementById("banReason").value;

		if (reason.length < 5) {
			alert("You must enter a reason for the ban.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/banGroup'); ?>",
				type: 'POST',
				data: {groupName: groupName, reason: reason},
				success: function(msg) {
					if (msg.length > 0) {
						alert(msg);
					}
					location.reload();
				}
			});
		}
	}

</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-4">
			<h4>Add New Ban</h4>
			<div class="input-group">
				<span class="input-group-addon">Group Name</span>
				<input type="text" class="form-control" id="groupName" name="groupName"></input>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Reason</span>
				<textarea class="form-control" id="banReason" rows="5"></textarea>
			</div>
			<br>
			<button class="btn btn-outline-success" type="button" onclick="banGroup()" value="Ban">Ban</button>
		</div>
	</div>
	<hr>
	<?php if($groupBans->num_rows() > 0){ ?>
		<div class="row justify-content-md-center">
			<h3>Currently Banned Groups</h3>
		</div>
		<div class="row justify-content-md-center">
			<?php foreach($groupBans->result() as $row){ ?>
				<div class="col-md-8">
					<div class="card">
						<div class="card-header">
							<?php echo $row->groupName; ?> <button type="button" class="btn btn-sm btn-outline-success" value="Unban" onclick="unBan(<?php echo $row->id; ?>)">Unban</button>
						</div>
						<span class="card-text" style="padding: .75rem 1.25rem;"><?php echo $row->reason; ?></span>
						<div class="card-footer">
							<?php printf("Banned on <strong>%s</strong> by <strong>%s</strong>", $row->banDate, $row->bannedBy); ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } else { ?>
		<h4>There are no banned groups currently.</h4>
	<?php }?>
</div>

