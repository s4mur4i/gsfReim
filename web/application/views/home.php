<script>
$(document).ready(function() {
	$('#payoutBox').on('hidden.bs.modal', function () {
	 location.reload();
	})
});
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
	function showAttackers(killID){
		var id = "#"+killID+"-attackers";
		var content = $(id).html();
		$("#fittingBody").html(content);
		$("#fitting").modal('show');
	}
	function payOut(killID){
		var pT = document.getElementById("payoutType");
		var payoutType = pT.options[pT.selectedIndex].value;
		var payoutAmount = document.getElementById("payoutStr").value;
		var payoutNotes = document.getElementById("payoutNotes").value;
		if(payoutType==0){
			alert("You must choose a payout type.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/payOut'); ?>",
				type: 'POST',
				data: {killID: killID, payoutType: payoutType, payoutAmount: payoutAmount, payoutNotes: payoutNotes},
				success: function(msg) {
					$("#payoutBoxBody").html(msg);
				}
			});
		}
	}
	function pay(killID){
		$.ajax({
			url: "<?php echo base_url('admin/payLoss'); ?>",
			type: 'POST',
			data: {killID: killID},
			success: function(msg) {
				$("#payoutBoxBody").html(msg);
				$("#payoutBox").modal('show');
			}
		});
	}
	function denyPayout(killID){
		var reason = prompt("Why is it being denied?");
		if(reason != null){
			$.ajax({
				url: "<?php echo base_url('admin/denyPayout'); ?>",
				type: 'POST',
				data: {killID: killID, reason: reason},
				success: function(msg) {
					alert(msg);
				}
			});
		}
	}
	function getPayoutInfo(killID){
		var pT = document.getElementById("payoutType");
		var payoutType = pT.options[pT.selectedIndex].value;
		var search = killID+'-'+payoutType;
		var preSearch = search+'-PRE';
		var reason = document.getElementById("reasonStr");
		var payoutBox = document.getElementById("payoutStr");

		var payoutAmount = document.getElementById(search).value;
		var prefix = document.getElementById(preSearch).value;
		var reasonStr = prefix+'-'+killID;

		reason.value = reasonStr;
		payoutBox.value = payoutAmount;

	}
	function refreshPayouts(killID){
		$.ajax({
			url: "<?php echo base_url('home/updateAvailablePayouts'); ?>",
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
	<?php if($err) { ?>
		<h4>There was an error logging in.</h4>
		<p><?php echo $errMessage; ?></p>
	<?php } else { ?>
		<div class="row justify-content-md-center">
			<div class="col-md-8">
				<?php if($this->session->userdata('vars')['isBanned'] == 1){ ?>
					<h3>You have been banned from reimbursement until: <?php echo $this->session->userdata('vars')['banEnd']; ?></h3>
					<h4>Reason:</h4>
					<pre><?php echo $this->session->userdata('banReason'); ?></pre>
				<?php } elseif($this->session->userdata('vars')['isReim'] == 1 || $this->session->userdata('vars')['isReimDir'] == 1) {?>
					<h4>There are currently <b><?php if($allKills){ echo $allKills->num_rows();}else{ echo "0";} ?></b> loss(es) that need to be processed.</h4>
					<?php if($kills->num_rows() > 0){ ?>
						<hr>
						<h4>My Reserved Losses</h4>
						<table class="table table-sm table-striped">
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
									<th>zKB Link</th>
									<th>Payout Type</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($kills->result() as $row){
									if($row->overPtCap == 1){
										$class = 'class="danger"';
									} else {
										$class = '';
									}?>
									<tr <?php echo $class; ?>>
										<td><button type="button" class="btn btn-sm btn-outline-info" onclick="showFit(<?php echo $row->killID; ?>)">Show Fit</button>&nbsp;<button type="button" class="btn btn-sm btn-outline-info" onclick="showAttackers(<?php echo $row->killID; ?>)">Attackers</button></td>
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
										<td><?php printf("<strong>%s</strong> (<i>%s</i>)",$row->corpName,$row->allianceName); ?></td>
										<td><?php echo $row->killTime; ?></td>
										<td><?php echo $row->sysName; ?></td>
										<td><?php echo $row->regName; ?></td>
										<td><?php echo $row->shipName; ?></td>
										<td><a href="https://zkillboard.com/kill/<?php echo $row->killID; ?>/" target="_blank">zKB</a></td>
										<td><button type="button" class="btn btn-sm btn-outline-success" value="Pay" onclick="pay(<?php echo $row->killID; ?>)">Pay</button>&nbsp;<button type="button" class="btn btn-sm btn-outline-danger" value="Deny" onclick="denyPayout(<?php echo $row->killID; ?>)">Deny</button>&nbsp;<button type="button" class="btn btn-sm btn-outline-info" value="Refresh" onclick="refreshPayouts(<?php echo $row->killID; ?>)">Refresh</button></td>
									</tr>
									<div id="<?php echo $row->killID; ?>-attackers" style="display: none;">
										<div style="margin-left: 15px; margin-right: 15px;">
											<?php
											if($row->attackers <> ''){
												$attackerArr = unserialize($row->attackers);
												$countAttackers = count($attackerArr); ?>
												<div class="row">
													<h4>ESI Link: </h4><p><code><?php echo $row->crest_link; ?></code></p>
												</div>
												<div class="row">
													<h4>Total Attackers: <b><?php echo $countAttackers; ?></b></h4>
												</div>
												<ul class="list-group">
													<?php
													foreach($attackerArr as $key => $value){
														$name = $key;
														$corp = $value['corporation'];
														$alli = $value['alliance'];
														$ship = $value['shipType'];
														$dmg = $value['damageDone'];
														$weapon = $value['weaponType']; ?>
														<li class="list-group-item"><?php printf("<strong>%s</strong>&nbsp;(%s)[%s]&nbsp;|&nbsp;Ship:&nbsp;<strong>%s</strong>&nbsp;-&nbsp;%s&nbsp;(%d)", $name, $corp, $alli, $ship, $weapon, $dmg); ?></li>
													<?php } ?>
												</ul>
											<?php } else { ?>
												<h4>This kill was submitted before the attackers were added, thus there is no data to show.</h4>
											<?php } ?>
										</div>
									</div>
									<div id="<?php echo $row->killID; ?>" style="display: none;">
										<?php $fitArr = unserialize($row->fit);
										$newFitArr = array();
										foreach($fitArr as $key => $value){

											$newFitArr[$key] = array_count_values(array_filter($value));
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
						<h3>You have not reserved any losses yet.</h3>
					<?php }
					} elseif($this->session->userdata('vars')['logged_in']) { ?>
						<h4>To submit a loss, please click the "Submit Loss" button in the navigation bar.</h4>
					<?php } else { ?>
						<h4>Please login.</h4>
					<?php } ?>
			</div>
		</div>
		<?php if($this->session->userdata('vars')['isReim'] == 1 || $this->session->userdata('vars')['isReimDir'] == 1){ ?>
			<hr>
			<div class="row justify-content-md-center">
				<div class="col-md-8 col-md-offset-1">
					<h4>Pending Losses</h4>
					<?php if($allKills->num_rows() > 0){ ?>
						<table class="table table-sm table-striped">
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
									<th>zKB Link</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($allKills->result() as $row){
									if($row->overPtCap == 1){
										$class = 'class="danger"';
									} else {
										$class = '';
									}?>
									<tr <?php echo $class; ?>>
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
										<td><?php printf("<strong>%s</strong> (<i>%s</i>)",$row->corpName,$row->allianceName); ?></td>
										<td><?php echo $row->killTime; ?></td>
										<td><?php echo $row->sysName; ?></td>
										<td><?php echo $row->regName; ?></td>
										<td><?php echo $row->shipName; ?></td>
										<td><a href="https://zkillboard.com/kill/<?php echo $row->killID; ?>" target="_blank">zKB</a></td>
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
						<h3>There are no pending losses.</h3>
					<?php }
					} ?>
			</div>
		</div>
	<?php } ?>
</div>
<div class="modal fade" id="payoutBox" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Information</h4>
      </div>
      <div class="modal-body" id="payoutBoxBody">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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
