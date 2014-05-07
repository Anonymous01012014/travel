<div id="log" class="col-md-3 col-md-offset-2">
	<h2 class="log-title">
		Latest Activities
	</h2>
	<hr style="border-color: #000"/>
	<div style="height: 25px; color: #E4F0F3;">
		<b>Open Activity</b>
	</div>
	
	<div class="open_list scroll-pane" style="height: 110px; overflow-y: scroll;background-color: #333;">
	<ul>
		<?php for($i= 0;$i<count($open_list) ; $i++){?>
		<li><?php echo $open_list[$i];?></li>
		<?php }?>
	</ul>
	</div>
	<br /><hr style="border-color: #000"/>
	<div style=" height: 25px; color: #E4F0F3;">
		<b>Close Activity</b>
	</div>
	<div class="close_list scroll-pane" style="height: 110px; overflow-y: scroll;background-color: #333;">
	<ul>
		<?php for($i= 0;$i<count($close_list) ; $i++){?>
		<li><?php echo $close_list[$i];?></li>
		<?php }?>
	</ul>
	</div>
	<br /><hr style="border-color: #000"/>
	<div style=" height: 25px; color: #E4F0F3;">
		<b>Accept/Reject Activity</b>
	</div>
	
	<div class="acc_rej_list scroll-pane" style="height: 110px; overflow-y: scroll; background-color: #333;">
	<ul>
		<?php for($i= 0;$i<count($acc_rej_list) ; $i++){?>
		<li><?php echo $acc_rej_list[$i];?></li>
		<?php }?>
	</ul>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('.scroll-pane').each(function(){
			$(this).jScrollPane();
			});
	});
</script>
