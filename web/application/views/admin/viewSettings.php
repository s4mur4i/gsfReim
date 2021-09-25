<script>
	$(document).ready(function() {
		$('#settingDetail').on('hidden.bs.modal', function () {
			location.reload();
		});
	});
	function getDetails(id){
		$.ajax({
			url: "<?php echo base_url('admin/getSettingDetails'); ?>",
			type: 'POST',
			data: {id: id},
			success: function(msg) {
				$('#settingDetailBody').html(msg);
				$("#settingDetail").modal('show');
			}
		});
	}
	function saveSetting(id){
		var value = document.getElementById("newSettingValue").value;
		if(value==''){
			alert("There cannot be an empty value.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/editSetting'); ?>",
				type: 'POST',
				data: {id: id, value: value},
				success: function(msg) {
					$('#settingDetailBody').html(msg);
				}
			});
		}
	}
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-4">
			<table class="table table-sm table-striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Value</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($settings->result() as $row){ ?>
						<tr>
							<td><?php echo $row->name; ?></td>
							<td><?php echo $row->value; ?></td>
							<td><button type="button" class="btn btn-sm btn-outline-info" onclick="getDetails(<?php echo $row->id; ?>)" value="Edit">Edit</button></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="modal fade" id="settingDetail" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Information</h4>
      </div>
      <div class="modal-body" id="settingDetailBody">
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->