<script>
	function unBan(id){
		$.ajax({
			url: "<?php echo base_url('admin/unbanUser'); ?>",
			type: 'POST',
			data: {banID: id},
			success: function(msg) {
				alert(msg);
				location.reload();
			}
		});
	}
</script>
<div class="col-md-12">
	<div class="row justify-content-md-center">
		<div class="col-md-10">
			<?php if($bannedUsers->num_rows(0) > 0){ ?>
				<div class="row justify-content-md-center">
					<h3>Currently Banned Users</h3>
				</div>
				<div class="row justify-content-md-center">
					<?php foreach($bannedUsers->result() as $row){ ?>
						<div class="col-md-3" style="padding: .75rem 1rem;">
							<div class="card">
								<div class="card-header">
									<?php echo $row->userName; ?> <button type="button" class="btn btn-sm btn-outline-success mr-auto" value="Unban" onclick="unBan(<?php echo $row->id; ?>)">Unban</button>
								</div>
								<div class="card-body" style="padding: .75rem 1.25rem;">
									<span class="card-text">
										<?php echo $row->reason; ?>
									</span>
								</div>
								<div class="card-footer">
									<?php printf("Banned on %s | Expires at %s", $row->banStart, $row->banEnd); ?>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<h4>There are no banned users currently.</h4>
			<?php }?>
		</div>
	</div>
</div>
