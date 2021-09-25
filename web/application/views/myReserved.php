<script>
	function resBlock(){
		var nREle = document.getElementById("numRes");
		var numRes = nREle.options[nREle.selectedIndex].value;

		<?php if($this->session->userdata("vars")['isCapDir'] == 0){ ?>
			capsOnly = 0;
		<?php } else { ?>
			var cOnly = document.getElementById("capsOnly");
			if(cOnly.checked){
				capsOnly = 1;
			} else {
				capsOnly = 0;
			}
		<?php } ?>
//		alert(numRes);
		$.ajax({
			url: "<?php echo base_url('home/claimBlock'); ?>",
			type: 'POST',
			data: {numRes: numRes, capsOnly: capsOnly},
			success: function(msg) {
				alert(msg);
				location.reload();
			}
		});
	}
	function releaseKill(killID){
		$.ajax({
			url: "<?php echo base_url('home/releaseKill'); ?>",
			type: 'POST',
			data: {killID: killID},
			success: function(msg) {
				alert(msg);
				location.reload();
			}
		});
	}
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-3">
			<h3>Reserve Losses</h3>
			<div class="input-group">
				<span class="input-group-addon" id="numResAddon">Number</span>
				<select id="numRes" class="form-control" aria-label="numRes" aria-describedby="numResAddon">
					<option value="10">10</option>
					<option value="15">15</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="50">50</option>
				</select>
			</div>
			<br>
			<?php
				$vars = $this->session->userdata("vars");
					if($vars['isCapDir'] == 1){ ?>
						<div class="input-group">
							<span class="input-group-addon" id="capsOnlyCheck">Caps Only?<input type="checkbox" value="t" id="capsOnly" aria-label="capsOnly" aria-describedby="capsOnlyCheck" /></span>
						</div>
					<?php }
				?>
				<br />
			<button type="button" class="btn btn-outline-success" onclick="resBlock()" value="Reserve">Reserve</button>
		</div>
		<div class="col-md-8">
			<h3>Your currently reserved losses</h3>
			<?php if($reserved->num_rows() > 0){ ?>
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<th>Victim</th>
							<th>Ship Type</th>
							<th>System</th>
							<th>Region</th>
							<th>Loss Time</th>
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
								<td><button type="button" class="btn btn-sm btn-outline-danger" value="Release" onclick="releaseKill(<?php echo $row->killID; ?>)">Release</button></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h3>You do not have any losses reserved yet.</h3>
			<?php }?>
		</div>
	</div>
</div>
