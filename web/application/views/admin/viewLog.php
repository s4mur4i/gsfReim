<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css">
<script>
	$(document).ready(function() {
		$('#logTable').DataTable({
			"pageLength": 100,
			"order": [3, 'desc']
		});
	});
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-8">
			<?php if($logdata->num_rows() > 0){ ?>
				<table class="table table-sm table-striped" id="logTable">
					<thead>
						<tr>
							<th>User</th>
							<th>Type</th>
							<th>Data</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($logdata->result() as $row){ ?>
							<tr>
								<td><?php echo $row->user; ?></td>
								<td><?php echo $row->type; ?></td>
								<td><?php echo $row->data; ?></td>
								<td><?php echo $row->eventtimedate; ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h4>There is no log data to display here.</h4>
			<?php } ?>
		</div>
	</div>
</div>
