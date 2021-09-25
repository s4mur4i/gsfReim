<div class="input-group">
	<span class="input-group-addon">Type Name</span>
	<input type="text" class="form-control" id="typeNameE" value="<?php echo $typeName; ?>" readonly></input>
</div>
<br>
<div class="input-group">
	<span class="input-group-addon">Payout Type</span>
	<input type="text" class="form-control" id="payoutType" value="<?php echo $payoutType; ?>" readonly></input>
</div>
<br>
<div class="input-group">
	<span class="input-group-addon">Payout Amount</span>
	<input type="text" class="form-control" id="payoutAmountE" value="<?php echo $payoutAmount; ?>"></input>
</div>
<input type="hidden" value="<?php echo $payoutTypeID; ?>" id="payoutTypeID"></input>
<br>
<button type="button" class="btn btn-success" onclick="editPayout(<?php echo $id; ?>)" value="Add Item">Save Changes</button>
