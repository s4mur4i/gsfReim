<div class="input-group">
	<span class="input-group-addon">Name</span>
	<input type="text" class="form-control" value="<?php echo $details->row(0)->name; ?>" readonly></input>
</div>
<br>
<div class="input-group">
	<span class="input-group-addon">Value</span>
	<input type="text" class="form-control" value="<?php echo $details->row(0)->value; ?>" id="newSettingValue"></input>
</div>
<br>
<button type="button" class="btn btn-outline-success" onclick="saveSetting(<?php echo $details->row(0)->id; ?>)" value="Save">Save</button>
