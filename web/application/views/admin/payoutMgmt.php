<script>
$(document).ready(function() {
	$('#payoutDetail').on('hidden.bs.modal', function () {
	 location.reload();
	});
	$("#typeName").autocomplete({
		source: "<?php echo base_url('/admin/getItemsAutocomplete'); ?>",
		cacheLength: 1,
		minLength: 3
	});
});
	function addPayout(){
		var typeName = document.getElementById("typeName").value;
		var payoutType = document.getElementById("payoutType");
		var payoutAmount = document.getElementById("payoutAmount").value;
		var selPT = payoutType.options[payoutType.selectedIndex].value;
		if(typeName==null || payoutType==0 || payoutAmount ==''){
		alert("You must enter an item name, select the payout type, and then enter an amount.");
		} else {
			$.ajax({
				url: "<?php echo base_url('admin/addPayout'); ?>",
				type: 'POST',
				data: {typeName: typeName, payoutType: selPT, payoutAmount: payoutAmount},
				success: function(msg) {
					if(msg != ''){
						alert(msg);
					}
					location.reload();
				}
			});
		}
	}
	
	function payoutDetail(id){
		$.ajax({
			url: "<?php echo base_url('admin/payoutDetail'); ?>",
			type: 'POST',
			data: {id: id},
			success: function(msg) {
				$("#payoutDetailBody").html(msg);
				$("#payoutDetail").modal('show');
			}
		});
	}
	
	function editPayout(id){
		var payoutAmount = document.getElementById("payoutAmountE").value;
		var payoutTypeID = document.getElementById("payoutTypeID").value;
		$.ajax({
			url: "<?php echo base_url('admin/editPayout'); ?>",
			type: 'POST',
			data: {id: id, payoutAmount: payoutAmount, payoutTypeID: payoutTypeID},
			success: function(msg) {
				$("#payoutDetailBody").html(msg);
			}
		});
	}
	
	function delPayout(id){
		$.ajax({
			url: "<?php echo base_url('admin/delPayout'); ?>",
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
		<div class="col-md-4">
			<h3>Add Item</h3>
			<div class="input-group">
				<span class="input-group-addon">Type Name</span>
				<input type="text" class="form-control" id="typeName" name="typeName"></input>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Payout Type</span>
				<select id="payoutType" class="form-control">
					<?php foreach($payoutTypes as $key => $value){ ?>
						<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
					<?php } ?>
				</select>
			</div>
			<br>
			<div class="input-group">
				<span class="input-group-addon">Payout Amount</span>
				<input type="text" class="form-control" id="payoutAmount"></input>
			</div>
			<br>
			<button type="button" class="btn btn-outline-success" onclick="addPayout()" value="Add Item">Add Item</button>
			<div id="payoutAddResult"></div>
		</div>
		<div class="col-md-5">
			<h3>Existing Items</h3>
			<?php if($payouts->num_rows() > 0){ ?>
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<th>Item Name</th>
							<th>Payout Type</th>
							<th>Payout Amount</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($payouts->result() as $row){ ?>
							<tr>
								<td><?php echo $row->typeName; ?></td>
								<td><?php echo $row->payoutType; ?></td>
								<td><?php echo number_format($row->payoutAmount,2); ?></td>
								<td><button type="button" class="btn btn-sm btn-outline-info" onclick="payoutDetail(<?php echo $row->id; ?>)" value="Edit">Edit</button></td>
								<td><button type="button" class="btn btn-sm btn-outline-info" onclick="delPayout(<?php echo $row->id; ?>)" value="Edit">Delete</button></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h3>No items have been added yet.</h3>
			<?php }?>
		</div>
	</div>
</div>
<div class="modal fade" id="payoutDetail" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Item Details</h4>
      </div>
      <div class="modal-body" id="payoutDetailBody"></div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->