<?php
	//if there is no acrive menu variable so leave it empty
	if(!isset($active_menu))
	{
		$active_menu =  "";
	}
?>


<div id="main-menu" class="row">
	<ul class="col-md-6 col-md-offset-2">
		<li><a href="<?php echo base_url();?>dashboard" class="<?php if($active_menu == "dashboard") echo 'active';?>">Dashboard</a></li>		
		<li><a href="#" class="<?php if($active_menu == "about") echo 'active';?>">About</a></li>
		<li><a href="#" class="<?php if($active_menu == "help") echo 'active';?>">Help</a></li>
	</ul>
	


</div>	