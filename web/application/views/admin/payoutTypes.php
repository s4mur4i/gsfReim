<script>
	function addPayoutType(){
		var typeName = document.getElementById("typeName").value;
		var prefix = document.getElementById("prefix").value;
		if(typeName==null || typeName=='' || prefix==null || prefix==""){
			alert("You must enter both a name AND prefix.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/addPayoutType'); ?>",
				type: 'POST',
				data: {typeName: typeName, prefix: prefix},
				success: function(msg) {
					if(msg != ''){
						alert(msg);
					}
					location.reload();
				}
			});
		}
	}
	function activatePayoutType(id){
		$.ajax({
			url: "<?php echo base_url('admin/activatePayoutType'); ?>",
			type: 'POST',
			data: {id: id},
			success: function(msg) {
				if(msg != ''){
						alert(msg);
					}
				location.reload();
			}
		});
	}
	function deactivatePayoutType(id){
		$.ajax({
			url: "<?php echo base_url('admin/deactivatePayoutType'); ?>",
			type: 'POST',
			data: {id: id},
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
		<div class="col-md-6">
			<div class="row justify-content-between">
				<div class="col-md-4">
					<h3>Add New Payout Type</h3>
					<div class="input-group">
						<span class="input-group-addon">Name</span>
						<input type="text" class="form-control" id="typeName"></input>
					</div>
					<br>
					<div class="input-group">
						<span class="input-group-addon">Prefix</span>
						<input type="text" class="form-control" id="prefix"></input>
					</div>
					<br>
					<button type="button" class="btn btn-outline-success" onclick="addPayoutType()" value="Add">Add</button>
					<div id="payoutTypeAddResult"></div>
				</div>
				<div class="col-md-4">
					<h3>Current Payout Types</h3>
					<?php if($types->num_rows() > 0){ ?>
						<table class="table table-sm table-striped">
							<thead>
								<tr>
									<th>Name</th>
									<th>Prefix</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($types->result() as $row){
									if($row->active == 1){
										$button = '<button type="button" class="btn btn-sm btn-outline-danger" value="Deactivate" onclick="deactivatePayoutType('.$row->id.')">Deactivate</button>';?>
										<tr class="success">
									<?php } else {
										$button = '<button type="button" class="btn btn-sm btn-outline-success" value="Activate" onclick="activatePayoutType('.$row->id.')">Activate</button>';?>
										<tr class="danger">
								<?php } ?>
									<td><?php echo $row->typeName; ?></td>
									<td><?php echo $row->prefix; ?></td>
									<td><?php echo $button; ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } else { ?>
						<h3>There are no payout types added yet.</h3>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
