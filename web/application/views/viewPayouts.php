<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/fixedheader/3.1.2/js/dataTables.fixedHeader.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.2/css/fixedHeader.dataTables.min.css">
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css">

<script>
	$(document).ready(function() {
		$('#payoutsTable').DataTable({
			"pageLength": 50
		});
	});
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-6">
			<div class="alert alert-info">
				<p><strong>New!</strong> Values in parenthesis next to payout amounts shows you the total amount of ISK you get from a loss, assuming platinum insurance.</p>
				<p>Format is: reimbursement amount ((platinum insurance payout - platinum insurance cost) + reimbursement amount)</p>
			</div>
		</div>
	</div>
	<div class="row justify-content-md-center">
		<div class="col-md-12">
			<?php if(count($payouts) > 0){ ?>
				<table class="table table-sm table-striped" id = "payoutsTable">
					<thead>
						<tr>
							<th>Ship Name</th>
							<?php foreach($payoutTypes->result() as $pt){ ?>
								<th><?php echo $pt->typeName; ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach($payouts as $key => $value){ ?>
							<tr>
								<td><?php echo $key; ?></td>
								<?php foreach($value as $k => $v){ ?>
									<td><b><?php echo number_format($v['payout']); ?></b> <?php if($v['payout'] > 0) { ?> (<?php echo number_format($v['totalReim']); ?>) <?php } ?></td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h4>There are no payouts to display.</h4>
			<?php } ?>
		</div>
	</div>
</div>
