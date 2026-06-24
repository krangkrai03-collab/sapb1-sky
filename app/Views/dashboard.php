<?php if (branding('dashboardNote')): ?>
	<div class="alert alert-success">
		<i class="fas fa-check-circle"></i> <?= esc(branding('dashboardNote')) ?>
	</div>
<?php endif; ?>

<style>
	/* การ์ดสูงเท่ากัน + footer ชิดล่างเสมอ */
	.dashboard-stats .small-box { height: 100%; display: flex; flex-direction: column; }
	.dashboard-stats .small-box .inner { flex: 1 1 auto; }
	.dashboard-stats .small-box-footer { margin-top: auto; }
</style>
<div class="row dashboard-stats">
	<div class="col-lg-4 col-6">
		<div class="small-box text-bg-info">
			<div class="inner"><h3><?= (int) $totalUsers ?></h3><p><?= lang('App.totalUsers') ?></p></div>
			<span class="small-box-icon"><i class="fas fa-users"></i></span>
			<?php if (user_can('users.view')): ?>
				<a href="<?= site_url('users') ?>" class="small-box-footer"><?= lang('App.viewList') ?> <i class="fas fa-arrow-circle-right"></i></a>
			<?php else: ?>
				<span class="small-box-footer">&nbsp;</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="col-lg-4 col-6">
		<div class="small-box text-bg-success">
			<div class="inner"><h3><?= (int) $totalGroups ?></h3><p><?= lang('App.totalGroups') ?></p></div>
			<span class="small-box-icon"><i class="fas fa-user-shield"></i></span>
			<?php if (user_can('roles.view')): ?>
				<a href="<?= site_url('roles') ?>" class="small-box-footer"><?= lang('App.viewList') ?> <i class="fas fa-arrow-circle-right"></i></a>
			<?php else: ?>
				<span class="small-box-footer">&nbsp;</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="col-lg-4 col-6">
		<div class="small-box text-bg-warning">
			<div class="inner"><h3 style="font-size:1.6rem"><?= esc(implode(', ', $myGroups)) ?></h3><p><?= lang('App.yourRoles') ?></p></div>
			<span class="small-box-icon"><i class="fas fa-id-badge"></i></span>
			<span class="small-box-footer">&nbsp;</span>
		</div>
	</div>
</div>
