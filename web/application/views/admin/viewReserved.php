<script>
	function releaseKill(killID){
		$.ajax({
			url: "<?php echo base_url('admin/admRelease'); ?>",
			type: 'POST',
			data: {killID: killID},
			success: function(msg) {
				if(msg != ''){
					alert(msg);
				}
				location.reload();
			}
		});
	}
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-8">
			<h3>Currently Reserved (unpaid) Losses</h3>
			<?php if($reserved->num_rows() > 0){ ?>
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<th>Victim</th>
							<th>Ship Type</th>
							<th>System</th>
							<th>Region</th>
							<th>Loss Time</th>
							<th>Reserved By</th>
							<th>Reserved On</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($reserved->result() as $row){ ?>
							<tr>
								<td><?php echo $row->victimName; ?></td>
								<td><?php echo $row->shipName; ?></td>
								<td><?php echo $row->sysName; ?></td>
								<td><?php echo $row->regName; ?></td>
								<td><?php echo $row->killTime; ?></td>
								<td><?php echo $row->reservedBy; ?></td>
								<td><?php echo $row->reservedDate; ?></td>
								<td><button type="button" class="btn btn-sm btn-outline-danger" value="Release" onclick="releaseKill(<?php echo $row->killID; ?>)">Release</button></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h3>There are no unpaid reserved losses.</h3>
			<?php }?>
		</div>
	</div>
</div>
