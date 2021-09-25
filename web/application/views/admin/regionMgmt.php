<script>
$(document).ready(function() {
	$("#regName").autocomplete({
		source: "<?php echo base_url('/admin/getRegionsAutocomplete'); ?>",
		cacheLength: 1,
		minLength: 3
	});
});
	function addRegion(){
		var regName = document.getElementById("regName").value;
		if(regName==null || regName==''){
			alert("You must enter a region name before submitting the form.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/addRegion'); ?>",
				type: 'POST',
				data: {regName: regName},
				success: function(msg) {
					if(msg != ''){
						alert(msg);
					}
					location.reload();
				}
			});
		}
	}
	
	function delRegion(regID){
		$.ajax({
			url: "<?php echo base_url('admin/delRegion'); ?>",
			type: 'POST',
			data: {regID: regID},
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
		<div class="col-md-7">
			<div class="row justify-content-between">
				<div class="col-md-4">
					<h3>Add a Region</h3>
					<div class="input-group">
						<span class="input-group-addon">Region Name</span>
						<input name="regName" id="regName" class="form-control" type="text"></input>
						<span class="input-group-btn"><button type="button" class="btn btn-outline-success" value="Add Region" onclick="addRegion()">Add Region</button></span>
					</div>
					<br>
				</div>
				<div class="col-md-4">
					<h3>Current Peacetime Regions</h3>
					<?php if($regions->num_rows() > 0){ ?>
						<table class="table table-sm table-striped">
							<thead>
								<tr>
									<th>Region</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($regions->result() as $row){ ?>
									<tr>
										<td><?php echo $row->regName; ?></td>
										<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="delRegion(<?php echo $row->regID; ?>)">Remove</button></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } else { ?>
						<h3>No Peacetime regions have been added yet.</h3>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
