<script>
	function activateUser(uid) {
		$.ajax({
			url: "<?php echo base_url('admin/activateUser'); ?>",
			type: 'POST',
			data: {userID: uid},
			success: function(msg) {
				location.reload();
			}
		});
	}
	function deactivateUser(uid) {
		$.ajax({
			url: "<?php echo base_url('admin/deactivateUser'); ?>",
			type: 'POST',
			data: {userID: uid},
			success: function(msg) {
				location.reload();
			}
		});
	}
</script>
<div class="col-md-4 col-md-offset-1">
	<?php if($users->num_rows() > 0){ ?>
		<h3>Current Users:</h3>
		<div class="row">
			<table class="table table-sm">
				<thead>
					<tr>
						<th></th>
						<th>User</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($users->result() as $row){ ?>
						<tr>
							<td><?php echo anchor("admin/edituser/" . $row->id, "Edit", 'class="btn btn-sm btn-info"'); ?></td>
							<td><?php echo $row->user; ?></td>
							<td>
								<?php if($row->active == 1){ ?>
									<button class="btn btn-sm btn-danger" value="Deactivate" onClick="deactivateUser(<?php echo $row->id; ?>)">Deactivate</button>
								<?php } else { ?>
									<button class="btn btn-sm btn-success" value="Activate" onClick="activateUser(<?php echo $row->id; ?>)">Activate</button>
								<?php }?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } else { ?>
		<h3>There are no users in the system.</h3>
	<?php } ?>
</div>
