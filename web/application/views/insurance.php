<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css">
<script>
	$(document).ready(function() {
		$('#ins').DataTable(
		                    {
		                    	lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
		                    	fixedHeader: true,
		                    	pageLength: 25
		                    }
		                );
	});
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<h3>Insurance Payouts by Ship</h3>
	</div>
	<div class="row justify-content-md-center">
		<?php if($insData->num_rows() > 0){ ?>
			<table class="table table-sm table-striped" id="ins">
				<thead>
					<tr>
						<th rowspan="2">Ship Name</th>
						<th colspan="2">Basic</th>
						<th colspan="2">Standard</th>
						<th colspan="2">Bronze</th>
						<th colspan="2">Silver</th>
						<th colspan="2">Gold</th>
						<th colspan="2">Platinum</th>
					</tr>
					<tr>
						<th>Cost</th>
						<th>Payout</th>
						<th>Cost</th>
						<th>Payout</th>
						<th>Cost</th>
						<th>Payout</th>
						<th>Cost</th>
						<th>Payout</th>
						<th>Cost</th>
						<th>Payout</th>
						<th>Cost</th>
						<th>Payout</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($insData->result() as $row){ ?>
						<tr>
							<td><?php echo $row->typeName; ?></td>
							<td><?php echo number_format($row->basicCost, 2); ?></td>
							<td><?php echo number_format($row->basicPayout,2); ?></td>
							<td><?php echo number_format($row->standardCost, 2); ?></td>
							<td><?php echo number_format($row->standardPayout,2); ?></td>
							<td><?php echo number_format($row->bronzeCost, 2); ?></td>
							<td><?php echo number_format($row->bronzePayout,2); ?></td>
							<td><?php echo number_format($row->silverCost, 2); ?></td>
							<td><?php echo number_format($row->silverPayout,2); ?></td>
							<td><?php echo number_format($row->goldCost, 2); ?></td>
							<td><?php echo number_format($row->goldPayout,2); ?></td>
							<td><?php echo number_format($row->platinumCost, 2); ?></td>
							<td><?php echo number_format($row->platinumPayout,2); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<h3>There is no data to display.</h3>
		<?php } ?>
	</div>
</div>