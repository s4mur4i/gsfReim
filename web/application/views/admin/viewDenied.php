<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css">
<script>
	$(document).ready(function() {
		$('#deniedTable').DataTable();
	});
</script>
<script>
	function showFit(killID){
		var id = "#"+killID;
		var content = $(id).html();
		$("#fittingBody").html(content);
		$("#fitting").modal('show');
	}
	function showBroadcast(killID){
		var id = "#"+killID+"bcast";
		var content = $(id).html();
		$("#fittingBody").html(content);
		$("#fitting").modal('show');
	}
</script>
<?php if($this->session->userdata('vars')['isReimDir'] == 1){ ?>
<script>
	function undeny(killID){
		$.ajax({
			url: "<?php echo base_url('admin/undenyPayout'); ?>",
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
<?php } ?>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-10">
			<?php if($denied->num_rows() > 0){ ?>
				<table class="table table-sm table-striped" id="deniedTable">
					<thead>
						<tr>
							<th></th>
							<th>Broadcast</th>
							<th>Victim</th>
							<th>Forum Name</th>
							<th>Corporation</th>
							<th>Kill Time</th>
							<th>System</th>
							<th>Region</th>
							<th>Ship</th>
							<th>Denied By</th>
							<th>Denied Reason</th>
							<th>Denied On</th>
							<?php if($this->session->userdata('vars')['isReimDir'] == 1){ ?>
							<th></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach($denied->result() as $row){ ?>
							<tr>
								<td><button type="button" class="btn btn-sm btn-outline-info" onclick="showFit(<?php echo $row->killID; ?>)">Show Fit</button></td>
								<?php if($row->bcast <> ''){ ?>
									<td><button type="button" class="btn btn-sm btn-outline-info" onclick="showBroadcast(<?php echo $row->killID; ?>)">Show Broadcast</button></td>
									<div id="<?php echo $row->killID . "bcast"; ?>" style="display: none;">
										<pre><?php echo $row->bcast; ?></pre>
									</div>
								<?php } else { ?>
									<td>No Broadcast</td>
								<?php } ?>
								<td id="vicName-<?php echo $row->killID; ?>"><?php echo $row->victimName; ?></td>
								<td><?php echo $row->submittedBy; ?></td>
								<td><?php echo $row->corpName; ?></td>
								<td><?php echo $row->killTime; ?></td>
								<td><?php echo $row->sysName; ?></td>
								<td><?php echo $row->regName; ?></td>
								<td><?php echo $row->shipName; ?></td>
								<td><?php echo $row->reservedBy; ?></td>
								<td><?php echo $row->reason; ?></td>
								<td><?php echo $row->deniedOn; ?></td>
								<?php if($this->session->userdata('vars')['isReimDir'] == 1){ ?>
								<td><button type="button" class="btn btn-outline-warning btn-sm" value="Undeny" onclick="undeny(<?php echo $row->killID; ?>)">Undeny</button></td>
								<?php } ?>
							</tr>
							<div id="<?php echo $row->killID; ?>" style="display: none;">
									<?php $fitArr = unserialize($row->fit);
									$newFitArr = array();
									foreach($fitArr as $key => $value){
										$newFitArr[$key] = array_count_values($value);
									}
									if(isset($newFitArr['high'])){
										echo "<h4>High</h4>";
										ksort($newFitArr['high']);
										foreach($newFitArr['high'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									if(isset($newFitArr['med'])){
										echo "<br>";
										echo "<h4>Mid</h4>";
										ksort($newFitArr['med']);
										foreach($newFitArr['med'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									if(isset($newFitArr['low'])){
										echo "<br>";
										echo "<h4>Low</h4>";
										ksort($newFitArr['low']);
										foreach($newFitArr['low'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									if(isset($newFitArr['rigs'])){
										echo "<br>";
										echo "<h4>Rigs</h4>";
										ksort($newFitArr['rigs']);
										foreach($newFitArr['rigs'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									if(isset($newFitArr['cargo'])){
										echo "<br>";
										echo "<h4>Cargo</h4>";
										ksort($newFitArr['cargo']);
										foreach($newFitArr['cargo'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									if(isset($newFitArr['other'])){
										echo "<br>";
										echo "<h4>Other</h4>";
										ksort($newFitArr['other']);
										foreach($newFitArr['other'] as $k => $v){
											echo "<p>" . $k . " - " . $v . "</p>";
										}
									}
									?>
								</div>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h3>There are no denied payouts.</h3>
			<?php } ?>
		</div>
	</div>
</div>
<div class="modal fade" id="fitting" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Information</h4>
      </div>
      <div class="modal-body" id="fittingBody">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
