<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-8">
			<div class="row justify-content-md-center">
				<h3>My Losses</h3>
			</div>
			<div class="row justify-content-md-center">
				<?php if($submitted->num_rows() > 0){
					foreach($submitted->result() as $row){
						switch($row->paid){
							case '0':
								$status = "PENDING";
								$note = "Your request has not been processed yet.";
							break;
							case '1':
								$status = "PAID";
								$note = "Payment Sent.<br>Reason Code: <strong>" .$row->prefix."-".$row->killID."</strong><br>Amount: <strong>".number_format($row->payoutAmount)."</strong><br>Processed On: <strong>".$row->paidOn."</strong>";
								if($row->note <> ''){
									$note .= "<br>Note From Reimburser: " . $row->note;
								}
							break;
							case '2':
								$status = "DENIED";
								$note = "Request Denied.<br>Reason: <pre><strong>" . $row->note."</strong></pre><br>Denied On: <strong>".$row->deniedOn."</strong>";
						}?>
						<div class="col-md-4" style="padding: .75rem 1.25rem;">
							<div class="card">
								<div class="card-header">
									<?php printf("<strong>%s</strong> lost in <strong>%s</strong> (<strong>%s</strong>) on <strong>%s</strong>", $row->shipName, $row->sysName, $row->regName, $row->killTime) ?>
								</div>
								<div class="card-body" style="padding: .75rem 1.25rem;">
									<span class="card-text">
										Character Name: <strong><?php echo $row->victimName; ?></strong> <?php if($row->reservedBy != ''){?>| Handled By: <strong><?php echo $row->reservedBy; } ?></strong><br />
										Status: <strong><?php echo $status; ?></strong><br />
										Note: <?php echo $note; ?>
										<?php if($row->bcast != "") { ?>
											<hr>
											Broadcast:
											<br />
											<?php echo $row->bcast;
										} ?>
									</span>
								</div>
								<div class="card-footer text-muted">
									<?php printf("Submitted on %s", $row->timestamp); ?>
								</div>
							</div>
						</div>
					<?php }
				} else { ?>
					<h3>You have not submitted any losses yet.</h3>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
